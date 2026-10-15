
<x-filament-panels::page>
    <form wire:submit="submit">
        @forelse ($collections as $pelajaran)
            <section class="mb-8">
                <h2 class="mb-4 text-lg font-bold">
                    {{ $pelajaran['name'] }}
                </h2>

                @forelse ($pelajaran['soals'] as $index => $pertanyaan)
                    <div class="mb-6 rounded-xl border p-4">
                        <div class="mb-4 font-medium">
                            {{ $index + 1 }}.
                            {!! $pertanyaan['payload'] !!}
                        </div>

                        <div class="space-y-3">
                            @forelse ($pertanyaan['answers'] as $jawaban)
                                <label class="flex cursor-pointer items-start gap-3">
                                    <input
                                        type="radio"
                                        name="answers_{{ $pertanyaan['id'] }}"
                                        value="{{ $jawaban['id'] }}"
                                        wire:model="answers.{{ $pertanyaan['id'] }}"
                                        class="mt-1"
                                    >

                                    <span>{{ $jawaban['text'] }}</span>
                                </label>
                            @empty
                                <p class="text-danger-600">
                                    Belum ada pilihan jawaban untuk soal ini.
                                </p>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <p>Belum ada soal aktif untuk mata pelajaran ini.</p>
                @endforelse
            </section>
        @empty
            <p>Belum ada mata pelajaran pada ujian ini.</p>
        @endforelse

        @if (count($collections) > 0)
            <div class="mt-6">
                <x-filament::button
                    type="submit"
                    color="success"
                    icon="heroicon-o-check-circle"
                    wire:confirm="Apakah kamu yakin ingin menyelesaikan ujian ini?"
                >
                    Selesaikan Ujian
                </x-filament::button>
            </div>
        @endif
    </form>
</x-filament-panels::page>
