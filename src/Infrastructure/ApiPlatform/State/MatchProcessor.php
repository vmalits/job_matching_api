<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Domain\Matching\Repository\MatchRepositoryInterface;
use App\Domain\User\Entity\User;
use App\Infrastructure\ApiPlatform\Resource\MatchResource;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements ProcessorInterface<MatchResource, MatchResource>
 */
final readonly class MatchProcessor implements ProcessorInterface
{
    public function __construct(
        private MatchRepositoryInterface $matchRepository,
        private MatchProvider $matchProvider,
        private Security $security,
    ) {
    }

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): MatchResource {
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();

        $match = $this->matchRepository->getById($uriVariables['id']);

        if ('accept' === $data->action) {
            $match->accept();
        } elseif ('reject' === $data->action) {
            $match->reject();
        }

        $this->matchRepository->save($match);

        $result = $this->matchProvider->provide(
            operation: $operation,
            uriVariables: ['id' => $match->getId()],
            context: $context,
        );

        if (!$result instanceof MatchResource) {
            throw new \LogicException('Expected MatchResource.');
        }

        return $result;
    }
}
