<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250208164916 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE deposit (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, deposit_method VARCHAR(255) NOT NULL, amount DOUBLE PRECISION DEFAULT NULL, deposit_proof VARCHAR(255) NOT NULL, narration VARCHAR(255) DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_95DB9D39A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `signal` (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, deposit_method VARCHAR(255) DEFAULT NULL, amount DOUBLE PRECISION DEFAULT NULL, signal_proof VARCHAR(255) DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_740C95F5A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE social (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, social VARCHAR(255) DEFAULT NULL, social_username VARCHAR(255) DEFAULT NULL, social_email VARCHAR(255) DEFAULT NULL, social_password VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_7161E187A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE trade (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, trading_type VARCHAR(255) DEFAULT NULL, currency_pair VARCHAR(255) DEFAULT NULL, lot_size DOUBLE PRECISION DEFAULT NULL, entry_price DOUBLE PRECISION DEFAULT NULL, stop_loss DOUBLE PRECISION DEFAULT NULL, take_profit DOUBLE PRECISION DEFAULT NULL, trading_action VARCHAR(255) DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_7E1A4366A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE upgrade (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, upgrade_plan VARCHAR(255) DEFAULT NULL, payment_method VARCHAR(255) DEFAULT NULL, proof VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_B766741AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, ref_code_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, fullname VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, country VARCHAR(255) NOT NULL, currency VARCHAR(255) NOT NULL, social_account VARCHAR(255) NOT NULL, social_account_contact VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', uid VARCHAR(255) NOT NULL, balance DOUBLE PRECISION DEFAULT NULL, is_verified TINYINT(1) DEFAULT NULL, earning DOUBLE PRECISION DEFAULT NULL, pending_withdrawal DOUBLE PRECISION DEFAULT NULL, active_deposits DOUBLE PRECISION DEFAULT NULL, last_deposit DOUBLE PRECISION DEFAULT NULL, id_card_front VARCHAR(255) DEFAULT NULL, id_card_back VARCHAR(255) DEFAULT NULL, verification_status VARCHAR(255) DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_8D93D649E3CEADC7 (ref_code_id), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE withdrawal (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, withdrawal_method VARCHAR(255) DEFAULT NULL, bitcoin_address VARCHAR(255) DEFAULT NULL, ethereum_address VARCHAR(255) DEFAULT NULL, litecoin_address VARCHAR(255) DEFAULT NULL, bitcoincash_address VARCHAR(255) DEFAULT NULL, skrill_email VARCHAR(255) DEFAULT NULL, paypal_email VARCHAR(255) DEFAULT NULL, bank_name VARCHAR(255) DEFAULT NULL, account_number VARCHAR(255) DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, swift_code VARCHAR(255) DEFAULT NULL, amount DOUBLE PRECISION DEFAULT NULL, fees VARCHAR(255) DEFAULT NULL, narration VARCHAR(255) DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_6D2D3B45A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE deposit ADD CONSTRAINT FK_95DB9D39A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE `signal` ADD CONSTRAINT FK_740C95F5A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE social ADD CONSTRAINT FK_7161E187A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE trade ADD CONSTRAINT FK_7E1A4366A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE upgrade ADD CONSTRAINT FK_B766741AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D649E3CEADC7 FOREIGN KEY (ref_code_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE withdrawal ADD CONSTRAINT FK_6D2D3B45A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE deposit DROP FOREIGN KEY FK_95DB9D39A76ED395');
        $this->addSql('ALTER TABLE `signal` DROP FOREIGN KEY FK_740C95F5A76ED395');
        $this->addSql('ALTER TABLE social DROP FOREIGN KEY FK_7161E187A76ED395');
        $this->addSql('ALTER TABLE trade DROP FOREIGN KEY FK_7E1A4366A76ED395');
        $this->addSql('ALTER TABLE upgrade DROP FOREIGN KEY FK_B766741AA76ED395');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D649E3CEADC7');
        $this->addSql('ALTER TABLE withdrawal DROP FOREIGN KEY FK_6D2D3B45A76ED395');
        $this->addSql('DROP TABLE deposit');
        $this->addSql('DROP TABLE `signal`');
        $this->addSql('DROP TABLE social');
        $this->addSql('DROP TABLE trade');
        $this->addSql('DROP TABLE upgrade');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE withdrawal');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
