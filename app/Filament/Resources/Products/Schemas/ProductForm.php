<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->rows(3),
                    ]),

                TextInput::make('name')
                    ->label('Product Name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $operation, $state, $set) {
                        if ($operation === 'create') {
                            $set('sku', 'SKU-' . strtoupper(Str::random(8)));
                        }
                    }),

                TextInput::make('sku')
                    ->label('SKU')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->helperText('Stock Keeping Unit - will be auto-generated if left empty'),

                Textarea::make('description')
                    ->label('Description')
                    ->rows(4)
                    ->columnSpanFull(),
                    
                Repeater::make('attributes')
                    ->label('Product Attributes')
                    ->relationship('attributes')
                    ->schema([
                        Select::make('type')
                            ->label('Type')
                            ->options([
                                'size' => 'Size',
                                'color' => 'Warna',
                            ])
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->columnSpan(1),

                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Large, Red, etc.')
                            ->helperText('Specific value for this attribute')
                            ->columnSpan(1),
                        FileUpload::make('image')
                        ->label('Attribute Image')
                        ->disk('public')
                        ->directory('product-attributes')
                        ->image()
                        ->imageEditor()
                        ->maxSize(5120)
                        ->previewable(true)
                        ->downloadable(false)
                        ->imagePreviewHeight('250')
                        ->columnSpanFull()
                        ->helperText('Click image to preview'),
                        TextInput::make('price_adjustment')
                            ->label('Price Adjustment')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp')
                            ->step(0.01)
                            ->helperText('Additional price for this attribute (use negative for discount)')
                            ->columnSpan(1),

                        TextInput::make('qty')
                            ->label('Quantity')
                            ->numeric()
                            ->default(0)
                            ->step(0.01)
                            ->suffix('units')
                            ->helperText('Available stock for this attribute')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->itemLabel(fn (array $state): ?string => 
                        ($state['type'] ?? '') . ($state['name'] ? ': ' . $state['name'] : '')
                    )
                    ->addActionLabel('Add Attribute')
                    ->reorderable()
                    ->cloneable()
                    ->columnSpanFull()
                    ->defaultItems(0),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->helperText('Inactive products will not be visible'),
            ])
            ->columns(2);
    }
}