<flux:modal name="pin-auth-modal" class="w-80">
    <div class="flex flex-col items-center p-6 space-y-6">
        <div class="text-center">
            <flux:heading size="lg">Autenticação</flux:heading>
            <flux:subheading>Introduza o seu PIN de acesso</flux:subheading>
        </div>

        <div class="w-full">
            <flux:input
                type="password"
                wire:model="pin"
                placeholder="••••"
                class="text-center text-2xl tracking-widest font-mono" readonly
            />
            <flux:error name="pin" />
            <div wire:loading wire:target="appendToPin, backspace, clear" class="absolute right-2 top-3">
                <flux:icon.loading class="text-zinc-400 w-4 h-4 animate-spin" />
            </div>
        </div>

        <div class="grid grid-cols-3 gap-1 w-full">
            @foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9] as $num)
                <flux:button square class="text-xl w-full h-14" wire:click="appendToPin('{{ $num }}')">{{ $num }}</flux:button>
            @endforeach

            <flux:button variant="danger" square wire:click="clearPin" class="h-14 text-xl w-full">C</flux:button>
            <flux:button
                variant="filled"
                class="h-14 text-xl font-bold bg-zinc-200 dark:bg-zinc-700" wire:click="appendToPin('0')">
                0
            </flux:button>
            <flux:button variant="primary" class="h-14 w-full" wire:click="backspacePin">⌫</flux:button>
        </div>

        <div class="flex gap-2">
            <flux:spacer />

            <flux:button variant="primary" color="green" wire:click="authenticateUser">Confirmar</flux:button>
            <flux:modal.close>
                <flux:button>Cancelar</flux:button>
            </flux:modal.close>
        </div>
    </div>
</flux:modal>
