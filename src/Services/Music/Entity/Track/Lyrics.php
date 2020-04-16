<?php

declare(strict_types=1);

namespace App\Services\Music\Entity\Track;

use App\Services\Music\Entity\BaseModel;

class Lyrics extends BaseModel
{
    public $id;
    public $lyrics;
    public $fullLyrics;
    public $hasRights;
    public $textLanguage;
    public $showTranslation;
}
