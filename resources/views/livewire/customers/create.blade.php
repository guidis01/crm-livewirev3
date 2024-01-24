<x-modal wire:model="modal" title="Create Customer" separator>

    <form wire:submit="save" id="create-customer-form">
        <div class="space-y-2">
            <x-input label="Name" wire:model="name"/>
            <x-input label="Email" wire:model="email"/>
            <x-input label="Phone" wire:model="phone"/>

        </div>
    </form>
    <x-slot:actions>
        <x-button label="Cancel" @click="$wire.modal = false"/>
        <x-button label="Save" type="submit" form="create-customer-form"/>
    </x-slot:actions>
</x-modal>
