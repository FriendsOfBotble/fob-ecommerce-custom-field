<?php

namespace FriendsOfBotble\EcommerceCustomField\Http\Controllers;

use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Supports\Breadcrumb;
use Botble\Ecommerce\Http\Controllers\BaseController;
use FriendsOfBotble\EcommerceCustomField\Forms\CustomFieldForm;
use FriendsOfBotble\EcommerceCustomField\Http\Requests\CustomFieldRequest;
use FriendsOfBotble\EcommerceCustomField\Models\CustomField;
use FriendsOfBotble\EcommerceCustomField\Tables\CustomFieldTable;

class CustomFieldController extends BaseController
{
    protected function breadcrumb(): Breadcrumb
    {
        return parent::breadcrumb()
            ->add(trans('plugins/fob-ecommerce-custom-field::custom-field.name'), route('ecommerce-custom-fields.index'));
    }

    public function index(CustomFieldTable $table)
    {
        $this->pageTitle(trans('plugins/fob-ecommerce-custom-field::custom-field.name'));

        return $table->renderTable();
    }

    public function create()
    {
        $this->pageTitle(trans('plugins/fob-ecommerce-custom-field::custom-field.create'));

        return CustomFieldForm::create()->renderForm();
    }

    public function store(CustomFieldRequest $request)
    {
        $form = CustomFieldForm::create()->setRequest($request);
        $form->saveOnlyValidatedData();

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('ecommerce-custom-fields.index'))
            ->setNextUrl(route('ecommerce-custom-fields.edit', $form->getModel()->getKey()))
            ->withCreatedSuccessMessage();
    }

    public function edit(CustomField $customField)
    {
        $this->pageTitle(trans('core/base::forms.edit_item', ['name' => $customField->label]));

        return CustomFieldForm::createFromModel($customField)->renderForm();
    }

    public function update(CustomField $customField, CustomFieldRequest $request)
    {
        CustomFieldForm::createFromModel($customField)
            ->setRequest($request)
            ->saveOnlyValidatedData();

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('ecommerce-custom-fields.index'))
            ->withUpdatedSuccessMessage();
    }

    public function destroy(CustomField $customField): DeleteResourceAction
    {
        return DeleteResourceAction::make($customField);
    }
}
