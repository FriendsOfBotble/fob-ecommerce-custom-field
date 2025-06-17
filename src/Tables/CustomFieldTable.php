<?php

namespace FriendsOfBotble\EcommerceCustomField\Tables;

use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\BulkChanges\CreatedAtBulkChange;
use Botble\Table\BulkChanges\NameBulkChange;
use Botble\Table\BulkChanges\StatusBulkChange;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\EnumColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\LinkableColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\Table\HeaderActions\CreateHeaderAction;
use FriendsOfBotble\EcommerceCustomField\Models\CustomField;
use Illuminate\Database\Eloquent\Builder;

class CustomFieldTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(CustomField::class)
            ->addHeaderAction(CreateHeaderAction::make()->route('ecommerce-custom-fields.create'))
            ->addActions([
                EditAction::make()->route('ecommerce-custom-fields.edit'),
                DeleteAction::make()->route('ecommerce-custom-fields.destroy'),
            ])
            ->addColumns([
                IdColumn::make(),
                LinkableColumn::make('label')->route('ecommerce-custom-fields.edit'),
                EnumColumn::make('display_location')
                    ->label(trans('plugins/fob-ecommerce-custom-field::custom-field.display_location')),
                CreatedAtColumn::make(),
                StatusColumn::make(),
            ])
            ->addBulkChanges([
                NameBulkChange::make(),
                StatusBulkChange::make(),
                CreatedAtBulkChange::make(),
            ])
            ->addBulkAction(DeleteBulkAction::make())
            ->queryUsing(fn (Builder $query) => $query->select([
                'id',
                'label',
                'display_location',
                'created_at',
                'status',
            ]));
    }
}
