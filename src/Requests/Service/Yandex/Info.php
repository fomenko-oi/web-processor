<?php

namespace App\Requests\Service\Yandex;

use Symfony\Component\Validator\Constraints as Assert;

class Info
{
    /**
     * @Assert\NotBlank(message="Id argument is required.")
     * @Assert\Type(
     *     type="integer",
     *     message="Is must to be an integer."
     * )
     */
    public int $id;
}
