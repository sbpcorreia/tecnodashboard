<div class="w-full h-full" xmlns:flex="http://www.w3.org/1999/html">

@if($interruptedWorkCenters->isEmpty())
    <div class="text-center text-gray-500 flex justify-center place-items-center h-full text-2xl">
        Não existem centros de trabalho interrompidos no momento.
    </div>
@else
    <div class="space-y-8"> {{-- Espaçamento vertical entre os grandes grupos --}}

        {{-- 1. Agrupamos a coleção pelo código do centro de trabalho --}}
        @foreach ($interruptedWorkCenters->groupBy('codct') as $codCt => $itemsDoGrupo)

            {{-- 2. Aqui desenhamos o "Container" do Grupo (Accordion ou Card Grande) --}}
            <div wire:key="group-{{ $codCt }}" class="border border-gray-200 rounded-lg overflow-hidden shadow-sm bg-white">

                {{-- Cabeçalho do Grupo (Ocupa a largura toda) --}}
                <div class="bg-gray-100 p-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="font-bold text-xl text-gray-800">
                        {{-- Como todos os itens do grupo têm o mesmo nome, pegamos do primeiro --}}
                        {{ $itemsDoGrupo->first()->desct }}
                    </h2>
                    <span class="text-xs bg-gray-200 px-2 py-1 rounded-full text-gray-600">
                        {{ $itemsDoGrupo->count() }} interrupções
                    </span>
                </div>

                {{-- 3. A Grid de 3 colunas vive DENTRO deste grupo --}}
                <div class="p-4 bg-gray-50">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        {{-- Loop interno apenas com os cartões deste grupo específico --}}
                        @foreach ($itemsDoGrupo as $workCenter)

                            <div wire:key="wc-{{ $workCenter->u_logtouchstamp }}" class="bg-yellow-400 p-4 border border-yellow-600 rounded-sm relative shadow-sm">

                                <flux:tooltip content="Alterar motivo de interrupção" placement="top">
                                    <flux:button variant="primary" color="orange" size="sm" square class="!absolute top-2 right-2" wire:click="$dispatch('open-pin-modal', { targetModal: 'set-interrupt-reason', parameters: '{{ $workCenter->u_logtouchstamp }}' })">
                                        <flux:icon.arrow-path-rounded-square />
                                    </flux:button>
                                </flux:tooltip>

                                <div x-data="{
    startTime: {{ $workCenter->stopped_at }},
    elapsed: '',
    calculateTime() {
        const now = Date.now();
        const diff = now - this.startTime;
        if (diff < 0) { this.elapsed = '00:00:00'; return; }

        const totalSeconds = Math.floor(diff / 1000);
        const hours = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
        const minutes = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
        const seconds = String(totalSeconds % 60).padStart(2, '0');

        this.elapsed = `${hours}:${minutes}:${seconds}`;
    }
}"
                                     x-init="calculateTime(); setInterval(() => calculateTime(), 1000)"
                                     class="mt-4 pt-3 border-t border-yellow-600/20 flex flex-col items-center justify-center bg-yellow-500/30 rounded-lg p-2 shadow-inner"
                                >
                                    <span class="text-[10px] uppercase font-bold text-yellow-900/60 leading-none mb-1">Tempo de Paragem</span>
                                    <div class="flex items-center gap-2">
                                        <flux:icon.clock class="w-5 h-5 text-red-700 animate-pulse" />
                                        <span class="text-2xl font-black font-mono text-red-700 tracking-tighter" x-text="elapsed"></span>
                                    </div>
                                </div>

                                <h1 class="font-bold text-md pr-8 text-gray-900">{{ $workCenter->numof }}</h1>
                                <p class="text-sm font-medium text-gray-800 mb-2">{{$workCenter->descop}}</p>
                                <p class="text-sm font-medium text-gray-800">{{$workCenter->ref}} - {{$workCenter->design}}</p>
                                <div class="mt-3 pt-3 border-t border-yellow-500/30">
                                    <span class="text-xs uppercase tracking-wide text-yellow-900/70">Motivo</span>
                                    <p class="font-bold text-yellow-950">{{ $workCenter->motivo }}</p>
                                </div>
                                <div class="mt-3 pt-3 flex flex-row justify-between items-center">
                                    <div class="flex flex-row items-center gap-2">
                                        <span class="text-yellow-900/70"><flux:icon.wrench variant="micro"/></span>
                                        <p class="text-md text-gray-800">{{$workCenter->nome}}</p>
                                    </div>
                                </div>
                                <div class="mt-3 pt-3 flex flex-row justify-between items-center">
                                    <div class="flex flex-row items-center gap-2">
                                        <span class="text-yellow-900/70"><flux:icon.user variant="micro"/></span>
                                        <p class="text-md text-gray-800">{{empty($workCenter->responsavel) ? 'Sem responsável definido' : $workCenter->responsavel }}</p>
                                    </div>
                                </div>

                            </div>
                        @endforeach

                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
    {{--
        Atualização automática: a contagem decrescente fica visível para o utilizador,
        avisa nos últimos 10 segundos e pára sempre que uma janela (PIN, motivo de
        paragem, operador, filtros) está aberta. Ao fechar a janela, recomeça do início.
    --}}
    <div class="!fixed bottom-6 right-6 flex items-center gap-2"
         x-data="{
            interval: {{ $refreshSeconds }},
            remaining: {{ $refreshSeconds }},
            deadline: 0,
            paused: false,
            refreshing: false,
            timer: null,

            init() {
                this.restart();
                this.timer = setInterval(() => this.tick(), 1000);
            },

            destroy() {
                clearInterval(this.timer);
            },

            tick() {
                // Qualquer modal do Flux aberto (é um <dialog open>) suspende a contagem
                if (document.querySelector('dialog[open]')) {
                    this.paused = true;
                    return;
                }

                if (this.paused) {
                    this.paused = false;
                    this.restart();
                    return;
                }

                if (this.refreshing) return;

                this.remaining = Math.max(0, Math.ceil((this.deadline - Date.now()) / 1000));

                if (this.remaining === 0) this.refreshNow();
            },

            restart() {
                this.deadline = Date.now() + this.interval * 1000;
                this.remaining = this.interval;
            },

            async refreshNow() {
                if (this.refreshing) return;
                this.refreshing = true;
                try {
                    await this.$wire.refreshWorkCenters();
                } finally {
                    this.refreshing = false;
                    this.restart();
                }
            },

            get warning() {
                return !this.paused && !this.refreshing && this.remaining <= 10;
            },

            get label() {
                if (this.refreshing) return 'A atualizar…';
                if (this.paused) return 'Atualização em pausa';
                if (this.warning) return `A atualizar em ${this.remaining} s`;
                const m = Math.floor(this.remaining / 60);
                const s = String(this.remaining % 60).padStart(2, '0');
                return `Próxima atualização em ${m}:${s}`;
            }
         }">

        <div class="flex items-center gap-2 h-10 px-3 rounded-lg border text-sm font-medium shadow-sm transition-colors"
             x-bind:class="warning
                ? 'bg-amber-400 border-amber-500 text-amber-950 animate-pulse'
                : (paused
                    ? 'bg-zinc-200 border-zinc-300 text-zinc-600 dark:bg-zinc-700 dark:border-zinc-600 dark:text-zinc-300'
                    : 'bg-white border-zinc-200 text-zinc-700 dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-200')">
            <span x-show="!paused && !refreshing"><flux:icon.clock variant="micro" /></span>
            <span x-show="paused" x-cloak><flux:icon.pause variant="micro" /></span>
            <span x-show="refreshing" x-cloak><flux:icon.arrow-path variant="micro" class="animate-spin" /></span>
            <span class="tabular-nums" x-text="label"></span>
        </div>

        @if($filteredData)
        <flux:button variant="primary" color="red" square wire:click="clearFilters">
            <flux:icon.x-mark />
        </flux:button>
        @endif
        <flux:button variant="primary" color="gray" square wire:click="$dispatch('open-wc-filters')">
            <flux:icon.funnel />
        </flux:button>
        <flux:tooltip content="Atualizar agora" placement="top">
            <flux:button variant="primary" color="blue" square x-on:click="refreshNow()" x-bind:disabled="refreshing">
                <flux:icon.arrow-path />
            </flux:button>
        </flux:tooltip>
    </div>

    <livewire:phc-user-auth />
    <livewire:work-center-filter-modal />
    <livewire:set-interrupt-reason />
    <livewire:set-operator />

    @if(!empty($messages))
        @if($messages["type"] == "error")
        <flux:callout class="!absolute bottom-6 left-6" variant="danger" icon="x-circle" heading="{{ $messages['message'] }}" />
        @else
        <flux:callout class="!absolute bottom-6 left-6" variant="success" icon="check-circle" heading="{{ $messages['message'] }}" />
        @endif
    @endif
</div>
