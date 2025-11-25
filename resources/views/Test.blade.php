<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    @foreach ($interruptedWorkCenters as $workCenter)
        <div class="card bg-red-100 p-4 border border-red-300">
            <h3 class="font-bold text-lg">
                {{ $workCenter->codct }}
            </h3>
            <div class="mt-2">
                <span class="text-sm text-gray-600">Motivo:</span>
                <p class="font-semibold">{{ $workCenter->motivo }}</p>
            </div>
            <div class="mt-2 text-xs text-gray-500">
                Parado desde: {{ $workCenter->data }}
            </div>
        </div>
    @endforeach
</div>
