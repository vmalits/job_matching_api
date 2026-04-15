<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260415173852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE candidate_profiles (user_id VARCHAR(36) NOT NULL, title VARCHAR(200) NOT NULL, bio TEXT NOT NULL, location VARCHAR(200) NOT NULL, experience_years INT NOT NULL, skills JSON NOT NULL, salary_min INT DEFAULT NULL, salary_max INT DEFAULT NULL, resume_url VARCHAR(500) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, id VARCHAR(36) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2A6EC7E3A76ED395 ON candidate_profiles (user_id)');
        $this->addSql('ALTER TABLE candidate_profiles ADD CONSTRAINT FK_2A6EC7E3A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE candidate_profiles DROP CONSTRAINT FK_2A6EC7E3A76ED395');
        $this->addSql('DROP TABLE candidate_profiles');
    }
}
