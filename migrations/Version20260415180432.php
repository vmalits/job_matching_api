<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260415180432 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE jobs (recruiter_id VARCHAR(36) NOT NULL, title VARCHAR(200) NOT NULL, description TEXT NOT NULL, company_name VARCHAR(200) NOT NULL, location VARCHAR(200) NOT NULL, employment_type VARCHAR(20) NOT NULL, status VARCHAR(20) NOT NULL, skills JSON NOT NULL, salary_min INT DEFAULT NULL, salary_max INT DEFAULT NULL, salary_visible BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, id VARCHAR(36) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('ALTER TABLE candidate_profiles DROP CONSTRAINT fk_2a6ec7e3a76ed395');
        $this->addSql('DROP INDEX uniq_2a6ec7e3a76ed395');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE jobs');
        $this->addSql('ALTER TABLE candidate_profiles ADD CONSTRAINT fk_2a6ec7e3a76ed395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_2a6ec7e3a76ed395 ON candidate_profiles (user_id)');
    }
}
