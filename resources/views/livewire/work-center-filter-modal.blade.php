<flux:modal name="wc-filter-modal" class="w-full max-w-md">
    <div class="flex flex-col items-center p-6 space-y-6">
        <div class="text-center">
            <flux:heading size="lg">Filtros</flux:heading>
        </div>
        <div class="w-full">
            <form wire:submit.prevent="applyFilters" class="w-full space-y-4">
            <flux:select
                class="w-full min-w-0"
                label="Centro de trabalho"
                variant="listbox"
                searchable
                multiple
                clearable
                placeholder="Selecionar centro de trabalho..."
                selected-suffix="centro(s) trabalho selecionado(s)"
                empty="Sem resultados"
                wire:model="selectedWorkCenters">
                <x-slot name="search">
                    <flux:select.search class="px-4" placeholder="Pesquisar..." />
                </x-slot>
                @foreach($workCenters as $workCenter)
                    <flux:select.option wire:key="{{ $workCenter['codct'] }}" value="{{ $workCenter['codct'] }}">{{ $workCenter['codct'] }} - {{ $workCenter['desct'] }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select
                class="w-full min-w-0"
                label="Motivo de interrupção"
                variant="listbox"
                searchable
                multiple
                clearable
                placeholder="Selecionar motivo..."
                selected-suffix="motivo(s) selecionado(s)"
                empty="Sem resultados"
                wire:model="selectedInterruptReasons">
                <x-slot name="search">
                    <flux:select.search class="px-4" placeholder="Pesquisar..." />
                </x-slot>
                @foreach($interruptReasons as $interruptReason)
                    <flux:select.option value="{{ $interruptReason['codigo'] }}">
                        {{ $interruptReason['descricao'] }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <div class="flex justify-end gap-2 mt-4">
                <flux:button type="submit" variant="primary" color="blue">Aplicar Filtros</flux:button>
                <flux:modal.close>
                    <flux:button>Cancelar</flux:button>
                </flux:modal.close>
            </div>
            </form>
        </div>
    </div>
</flux:modal>
