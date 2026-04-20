<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Domain\CandidateProfile\Repository\CandidateProfileRepositoryInterface;
use App\Domain\Matching\Repository\MatchRepositoryInterface;
use App\Domain\User\Entity\User;
use App\Infrastructure\ApiPlatform\Resource\MatchResource;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProcessorInterface<MatchResource, MatchResource>
 */
final readonly class MatchProcessor implements ProcessorInterface
{
    public function __construct(
        private MatchRepositoryInterface $matchRepository,
        private CandidateProfileRepositoryInterface $profileRepository,
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

        if ($currentUser->isCandidate()) {
            $profile = $this->profileRepository->findByUserId($currentUser->getId());
            if (null === $profile || $profile->getId() !== $match->getCandidateProfileId()) {
                throw new NotFoundHttpException('Not Found');
            }
        } else {
            throw new AccessDeniedHttpException('Only candidates can modify match status.');
        }

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
