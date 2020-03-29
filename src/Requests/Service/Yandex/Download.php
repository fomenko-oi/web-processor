<?php

namespace App\Requests\Service\Yandex;

use Symfony\Component\Validator\Constraints as Assert;

class Download
{
    const BITRATE_CHOICES = [320, 192];

    /**
     * @Assert\NotBlank(message="Id argument is required.")
     * @Assert\Type(
     *     type="integer",
     *     message="Is must to be an integer."
     * )
     */
    public int $id;

    /**
     * @Assert\NotBlank(message="Bitrate argument is required.")
     * @Assert\Type(
     *     type="integer",
     *     message="Is must to be an integer."
     * )
     * @Assert\Choice(choices=Download::BITRATE_CHOICES, message="Pass a valid bitrate value.")
     */
    public int $bitrate;
}
