@extends('layouts.app')

@section('title', 'Tambah Tiket Histori Baru')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('tickets.index') }}" class="text-xs font-medium text-sky-400 hover:text-sky-300 inline-flex items-center gap-1.5 mb-2">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Tiket
        </a>
        <h1 class="font-display text-2xl sm:text-3xl font-bold text-white">Tambah Histori Tiket Baru</h1>
        <p class="text-slate-400 text-sm mt-1">Masukkan rincian tiket keberangkatan, pemesan (Booker), dan pembayar (Payer).</p>
    </div>

    <div class="glass-card p-6 sm:p-8 rounded-2xl shadow-2xl">
        <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Section 1: Informasi Perjalanan -->
            <div>
                <h3 class="text-sm font-semibold text-sky-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-route"></i> Informasi Perjalanan
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="ticket_date" class="block text-xs font-medium text-slate-300 mb-1.5">
                            Tanggal Tiket / Keberangkatan <span class="text-rose-400">*</span>
                        </label>
                        <input type="date" id="ticket_date" name="ticket_date" value="{{ old('ticket_date', date('Y-m-d')) }}" required class="w-full glass-input rounded-xl px-4 py-2.5 text-sm bg-slate-900 @error('ticket_date') border-rose-500 @enderror">
                        @error('ticket_date')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="ticket_code" class="block text-xs font-medium text-slate-300 mb-1.5">
                            Kode Tiket / Ref Booking <span class="text-slate-500">(Opsional, otomatis jika kosong)</span>
                        </label>
                        <input type="text" id="ticket_code" name="ticket_code" value="{{ old('ticket_code') }}" placeholder="Contoh: GA-89102, TCK-2026-001" class="w-full glass-input rounded-xl px-4 py-2.5 text-sm placeholder-slate-600 @error('ticket_code') border-rose-500 @enderror">
                        @error('ticket_code')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="origin" class="block text-xs font-medium text-slate-300 mb-1.5">
                            Dari (Lokasi Keberangkatan) <span class="text-rose-400">*</span>
                        </label>
                        <input type="text" id="origin" name="origin" value="{{ old('origin') }}" placeholder="Contoh: Jakarta (CGK), Bandung" required class="w-full glass-input rounded-xl px-4 py-2.5 text-sm placeholder-slate-600 @error('origin') border-rose-500 @enderror">
                        @error('origin')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="destination" class="block text-xs font-medium text-slate-300 mb-1.5">
                            Ke (Lokasi Tujuan) <span class="text-rose-400">*</span>
                        </label>
                        <input type="text" id="destination" name="destination" value="{{ old('destination') }}" placeholder="Contoh: Surabaya (SUB), Bali" required class="w-full glass-input rounded-xl px-4 py-2.5 text-sm placeholder-slate-600 @error('destination') border-rose-500 @enderror">
                        @error('destination')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="transport_type" class="block text-xs font-medium text-slate-300 mb-1.5">
                            Jenis Transportasi <span class="text-rose-400">*</span>
                        </label>
                        <select id="transport_type" name="transport_type" required class="w-full glass-input rounded-xl px-4 py-2.5 text-sm bg-slate-900 @error('transport_type') border-rose-500 @enderror">
                            <option value="">-- Pilih Jenis Transportasi --</option>
                            @foreach($transportOptions as $option)
                                <option value="{{ $option }}" {{ old('transport_type') == $option ? 'selected' : '' }}>{{ $option }}</option>
                            @endforeach
                        </select>
                        @error('transport_type')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="passenger_name" class="block text-xs font-medium text-slate-300 mb-1.5">
                            Nama Penumpang <span class="text-rose-400">*</span>
                        </label>
                        <input type="text" id="passenger_name" name="passenger_name" value="{{ old('passenger_name', Auth::user()->name) }}" placeholder="Nama orang yang bepergian" required class="w-full glass-input rounded-xl px-4 py-2.5 text-sm placeholder-slate-600 @error('passenger_name') border-rose-500 @enderror">
                        @error('passenger_name')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <hr class="border-slate-800/80">

            <!-- Section 2: Pemesanan (Booker) & Pembayaran (Payer) -->
            <div>
                <h3 class="text-sm font-semibold text-emerald-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-credit-card"></i> Detail Booker (Pemesan) & Payer (Pembayar)
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Siapa yang Booking Text -->
                    <div>
                        <label for="booked_by" class="block text-xs font-medium text-slate-300 mb-1.5">
                            Siapa yang Booking (Nama/Instansi) <span class="text-rose-400">*</span>
                        </label>
                        <input type="text" id="booked_by" name="booked_by" value="{{ old('booked_by', Auth::user()->name) }}" placeholder="Nama pemesan tiket" required class="w-full glass-input rounded-xl px-4 py-2.5 text-sm placeholder-slate-600 @error('booked_by') border-rose-500 @enderror">
                        @error('booked_by')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Link Akun Booker User -->
                    <div>
                        <label for="booked_by_user_id" class="block text-xs font-medium text-slate-300 mb-1.5">
                            Linkkan dengan Akun Booker (Sistem)
                        </label>
                        <select id="booked_by_user_id" name="booked_by_user_id" class="w-full glass-input rounded-xl px-4 py-2.5 text-sm bg-slate-900">
                            <option value="">-- Pilih Akun User Booker --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('booked_by_user_id', Auth::id()) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ ucfirst($user->role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Siapa yang Bayar Text -->
                    <div>
                        <label for="paid_by" class="block text-xs font-medium text-slate-300 mb-1.5">
                            Siapa yang Bayar (Nama/Instansi) <span class="text-rose-400">*</span>
                        </label>
                        <input type="text" id="paid_by" name="paid_by" value="{{ old('paid_by', 'PT Corporate Finance') }}" placeholder="Nama pembayar / Perusahaan" required class="w-full glass-input rounded-xl px-4 py-2.5 text-sm placeholder-slate-600 @error('paid_by') border-rose-500 @enderror">
                        @error('paid_by')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Link Akun Payer User -->
                    <div>
                        <label for="paid_by_user_id" class="block text-xs font-medium text-slate-300 mb-1.5">
                            Linkkan dengan Akun Payer (Sistem)
                        </label>
                        <select id="paid_by_user_id" name="paid_by_user_id" class="w-full glass-input rounded-xl px-4 py-2.5 text-sm bg-slate-900">
                            <option value="">-- Pilih Akun User Payer --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('paid_by_user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ ucfirst($user->role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tanggal Bayar -->
                    <div>
                        <label for="payment_date" class="block text-xs font-medium text-slate-300 mb-1.5">
                            Tanggal Pembayaran <span class="text-slate-500">(Jika sudah dibayar)</span>
                        </label>
                        <input type="date" id="payment_date" name="payment_date" value="{{ old('payment_date') }}" class="w-full glass-input rounded-xl px-4 py-2.5 text-sm bg-slate-900 @error('payment_date') border-rose-500 @enderror">
                        @error('payment_date')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Harga / Biaya (IDR) -->
                    <div>
                        <label for="amount" class="block text-xs font-medium text-slate-300 mb-1.5">
                            Harga / Biaya Tiket (IDR) <span class="text-rose-400">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs font-mono font-bold">
                                Rp
                            </div>
                            <input type="number" step="1000" min="0" id="amount" name="amount" value="{{ old('amount', '0') }}" placeholder="1500000" required class="w-full glass-input rounded-xl pl-10 pr-4 py-2.5 text-sm font-mono placeholder-slate-600 @error('amount') border-rose-500 @enderror">
                        </div>
                        @error('amount')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status Pembayaran -->
                    <div>
                        <label for="status" class="block text-xs font-medium text-slate-300 mb-1.5">
                            Status Pembayaran <span class="text-rose-400">*</span>
                        </label>
                        <select id="status" name="status" required class="w-full glass-input rounded-xl px-4 py-2.5 text-sm bg-slate-900 @error('status') border-rose-500 @enderror">
                            @foreach($statusOptions as $optStatus)
                                <option value="{{ $optStatus }}" {{ old('status', 'Lunas') == $optStatus ? 'selected' : '' }}>{{ $optStatus }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Upload Bukti Tiket -->
                    <div>
                        <label for="attachment" class="block text-xs font-medium text-slate-300 mb-1.5">
                            Upload Bukti / Nota Tiket <span class="text-slate-500">(PDF, JPG, PNG max 5MB)</span>
                        </label>
                        <input type="file" id="attachment" name="attachment" accept=".pdf,.jpg,.jpeg,.png" class="w-full glass-input rounded-xl px-3 py-2 text-xs bg-slate-900 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-500/20 file:text-sky-300 hover:file:bg-sky-500/30">
                        @error('attachment')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <hr class="border-slate-800/80">

            <!-- Section 3: Catatan Tambahan -->
            <div>
                <label for="notes" class="block text-xs font-medium text-slate-300 mb-1.5">
                    Catatan / Keterangan Tambahan
                </label>
                <textarea id="notes" name="notes" rows="3" placeholder="Informasi tambahan seperti nomor kursi, kelas penerbangan, atau keperluan dinas..." class="w-full glass-input rounded-xl p-4 text-sm placeholder-slate-600">{{ old('notes') }}</textarea>
            </div>

            <!-- Submit buttons -->
            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="{{ route('tickets.index') }}" class="px-5 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-400 hover:to-blue-500 shadow-lg shadow-sky-500/25 transition-all">
                    <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan Tiket
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
