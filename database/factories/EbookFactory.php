<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EbookFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(3);

        $price = fake()->numberBetween(150, 2000);

        return [
            'title' => $title,
            'slug' => Str::slug($title . '-' . fake()->unique()->numberBetween(1, 9999)),
            'description' => fake()->paragraph(10),
            'author' => fake()->name(),
            'pdf_path' => 'pdfs/sample.pdf',
            'price' => $price,
            'price_in_cents' => $price * 100,
            'is_published' => true,
        ];
    }
}
