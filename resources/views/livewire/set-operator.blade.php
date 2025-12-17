<flux:modal name="set-operator-modal" class="w-full max-w-md">
    <div class="flex flex-col items-center p-6 space-y-6">
        <div class="text-center">
            <flux:heading size="lg">Alterar responsável</flux:heading>
        </div>
        <div class="w-full">
            <div class="max-w-sm rounded bg-white overflow-hidden shadow-lg">
                <div class="px-6 py-4">
                    <flux:subheading>Detalhes</flux:subheading>
                    <dl class="divide-y divide-gray-100">
                        <dt class="text-sm/6 font-medium text-gray-900">Centro de trabalho</dt>
                        <dd class="mt-1 text-sm/6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $workCenterCode }} - {{ $workCenterDescription }}</dd>
                        <dt class="text-sm/6 font-medium text-gray-900">Ordem de fabrico / Operação</dt>
                        <dd class="mt-1 text-sm/6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $workOrderCode }} / {{ $operationDescription }}</dd>
                        <dt class="text-sm/6 font-medium text-gray-900">Funcionário atual</dt>
                        <dd class="mt-1 text-sm/6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $currentEmployee }}</dd>
                        <dt class="text-sm/6 font-medium text-gray-900">Utilizador atual</dt>
                        <dd class="mt-1 text-sm/6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $user["nome"] ?? '' }}</dd>
                    </dl>
                </div>
            </div>

        </div>
        <div class="w-full">
            <form wire:submit.prevent="setNewOperator" class="w-full space-y-4">
                <flux:select
                    label="Responsável"
                    variant="listbox"
                    searchable
                    clearable
                    placeholder="Selecionar responsável..."
                    empty="Sem resultados"
                    wire:model="operator">
                    <x-slot name="search">
                        <flux:select.search class="px-4" placeholder="Pesquisar..." />
                    </x-slot>
                    @foreach($operators as $item)
                        <flux:select.option value="{{ $item['pestamp'] }}">
                            {{ $item['nome'] }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <div class="flex justify-end gap-2 mt-4">
                    <flux:button type="submit" variant="primary" color="blue">Alterar responsável</flux:button>
                    <flux:modal.close>
                        <flux:button>Cancelar</flux:button>
                    </flux:modal.close>
                </div>
            </form>
        </div>
    </div>
</flux:modal>
