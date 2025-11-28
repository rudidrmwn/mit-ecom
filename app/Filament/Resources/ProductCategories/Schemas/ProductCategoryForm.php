<?php

namespace App\Filament\Resources\ProductCategories\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextArea;

class ProductCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nama kategori Produk')->required()->maxLength(255),
                TextInput::make('slug')->label('Slug')->required()->maxLength(255),
                FileUpload::make('image')->image(),
                TextArea::make('description')->label('Deskripsi Kategori Produk')
            ]);
    }
}
