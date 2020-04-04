<?php

declare(strict_types=1);

namespace App\Entity\Main\Contact;

use Symfony\Component\Validator\Constraints as Assert;

class Contact
{
    /**
     * @Assert\Length(min="4", max="2000")
     */
    public ?string $message = null;
    /**
     * @Assert\Email()
     */
    public ?string $email = null;
}
