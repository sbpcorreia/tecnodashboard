<flux:modal name="wc-filter-modal" class="w-96">
    <div class="flex flex-col items-center p-6 space-y-6">
        <div class="text-center">
            <flux:heading size="lg">Filtros</flux:heading>
        </div>
        <div class="w-full">
            <form wire:submit.prevent="applyFilters" class="w-full space-y-4"">
            <flux:select
                label="Centro de trabalho"
                wire:model="selectedWorkCenters">
                    <flux:select.option value=""></flux:select.option>
                @foreach($workCenters as $workCenter)
                    <flux:select.option value="{{ $workCenter['codct'] }}">
                        {{ $workCenter['codct'] }} - {{ $workCenter['desct'] }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:select
                label="Motivo de interrupção"
                wire:model="selectedInterruptReasons">
                    <flux:select.option value=""></flux:select.option>
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
