<?php

namespace App\Livewire\Traits;

trait WithBreadcrumbs
{
    protected array $breadcrumbs = [];

    protected function homeBreadcrumb(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'url' => null],
        ];
    }

    protected function breadcrumb(string $title): void
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => $title, 'url' => null],
        ];
    }

    protected function breadcrumbMulti(string $parent, string $child): void
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'url' => route('dashboard')],
            ['label' => $parent, 'url' => null],
            ['label' => $child, 'url' => null],
        ];
    }
}
