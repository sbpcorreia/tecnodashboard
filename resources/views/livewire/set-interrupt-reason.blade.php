<flux:modal name="set-interrupt-reason-modal" class="w-96">
    <div class="flex flex-col items-center p-6 space-y-6">
        <div class="text-center">
            <flux:heading size="lg">Definir Motivo de Interrupção</flux:heading>



        </div>
        <div class="w-full">
            <flux:subheading>Detalhes</flux:subheading>
            <dl class="divide-y divide-gray-100">
                <dt class="text-sm/6 font-medium text-gray-900">Centro de trabalho</dt>
                <dd class="mt-1 text-sm/6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $workCenterCode }} - {{ $workCenterDescription }}</dd>
                <dt class="text-sm/6 font-medium text-gray-900">Ordem de fabrico / Operação</dt>
                <dd class="mt-1 text-sm/6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $workOrderCode }} / {{ $operationDescription }}</dd>
                <dt class="text-sm/6 font-medium text-gray-900">Utilizador atual</dt>
                <dd class="mt-1 text-sm/6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $user["nome"] ?? '' }}</dd>
            </dl>

        </div>
        <div class="w-full">
            <form wire:submit.prevent="applyNewReasonToOperation" class="w-full space-y-4"">
            <flux:select
                label="Motivo de Interrupção"
                wire:model="reason">
                    <flux:select.option value=""></flux:select.option>
                @foreach($reasons as $interruptReason)
                    <flux:select.option value="{{ $interruptReason['codigo'] }}">
                        {{ $interruptReason['descricao'] }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <div class="flex justify-end gap-2 mt-4">
                <flux:button type="submit" variant="primary" color="blue">Aplicar Motivo</flux:button>
                <flux:modal.close>
                    <flux:button>Cancelar</flux:button>
                </flux:modal.close>
            </div>
            </form>
        </div>
    </div>
</flux:modal>
