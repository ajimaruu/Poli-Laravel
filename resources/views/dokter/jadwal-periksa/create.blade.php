<x-layouts.app title="Tambah Jadwal Periksa">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('jadwal-periksa.index') }}" class="flex items-center justify-center w-9 h-9 
                  rounded-lg bg-slate-100 hover:bg-slate-200 
                  text-slate-600 transition">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>

        <h2 class="text-2xl font-bold text-slate-800">
            Tambah Jadwal Periksa
        </h2>
    </div>

    {{-- Card --}}
    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
        <div class="card-body p-8">

            <form action="{{ route('jadwal-periksa.store') }}" method="POST">
                @csrf

                {{-- Hari --}}
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                        Hari <span class="text-red-500">*</span>
                    </label>
                    <select name="hari"
                        class="w-full px-4 py-2 rounded-lg border-2 border-slate-300
                               focus:border-primary focus:outline-none
                               @error('hari') border-red-500 @enderror"
                        required>
                        <option value="">-- Pilih Hari --</option>
                        @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $hari)
                        <option value="{{ $hari }}" @selected(old('hari') === $hari)>{{ $hari }}</option>
                        @endforeach
                    </select>
                    @error('hari')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Grid Jam --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

                    {{-- Jam Mulai --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Jam Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" name="jam_mulai" value="{{ old('jam_mulai') }}"
                            class="w-full px-4 py-2 rounded-lg border-2 border-slate-300
                                   focus:border-primary focus:outline-none
                                   @error('jam_mulai') border-red-500 @enderror"
                            required>
                        @error('jam_mulai')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Jam Selesai --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">
                            Jam Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" name="jam_selesai" value="{{ old('jam_selesai') }}"
                            class="w-full px-4 py-2 rounded-lg border-2 border-slate-300
                                   focus:border-primary focus:outline-none
                                   @error('jam_selesai') border-red-500 @enderror"
                            required>
                        @error('jam_selesai')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                {{-- Buttons --}}
                <div class="flex gap-3">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary 
                               hover:bg-primary/90 text-white 
                               font-semibold text-sm transition">
                        <i class="fas fa-save mr-1"></i>
                        Simpan
                    </button>

                    <a href="{{ route('jadwal-periksa.index') }}" class="px-6 py-2.5 rounded-xl bg-slate-100 
                              hover:bg-slate-200 text-slate-600 
                              font-semibold text-sm transition">
                        Batal
                    </a>
                </div>

            </form>
        </div>
    </div>

</x-layouts.app>