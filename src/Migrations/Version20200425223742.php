<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200425223742 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE service_yandex_albums (id UUID NOT NULL, album_id INT NOT NULL, name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, bitrate INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN service_yandex_albums.id IS \'(DC2Type:yandex_album_id)\'');
        $this->addSql('COMMENT ON COLUMN service_yandex_albums.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE service_yandex_albums_tracks (album_id UUID NOT NULL, track_id UUID NOT NULL, PRIMARY KEY(album_id, track_id))');
        $this->addSql('CREATE INDEX IDX_B8A3E85C1137ABCF ON service_yandex_albums_tracks (album_id)');
        $this->addSql('CREATE INDEX IDX_B8A3E85C5ED23C43 ON service_yandex_albums_tracks (track_id)');
        $this->addSql('COMMENT ON COLUMN service_yandex_albums_tracks.album_id IS \'(DC2Type:yandex_album_id)\'');
        $this->addSql('COMMENT ON COLUMN service_yandex_albums_tracks.track_id IS \'(DC2Type:yandex_track_id)\'');
        $this->addSql('ALTER TABLE service_yandex_albums_tracks ADD CONSTRAINT FK_B8A3E85C1137ABCF FOREIGN KEY (album_id) REFERENCES service_yandex_albums (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE service_yandex_albums_tracks ADD CONSTRAINT FK_B8A3E85C5ED23C43 FOREIGN KEY (track_id) REFERENCES service_yandex_tracks (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE service_yandex_albums_tracks DROP CONSTRAINT FK_B8A3E85C1137ABCF');
        $this->addSql('DROP TABLE service_yandex_albums');
        $this->addSql('DROP TABLE service_yandex_albums_tracks');
        $this->addSql('ALTER TABLE service_yandex_tracks ALTER progress SET DEFAULT 0');
    }
}
