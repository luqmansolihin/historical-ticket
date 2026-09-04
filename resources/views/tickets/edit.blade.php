@extends('layouts.app')

@section('title', 'Edit Tiket - ' . $ticket->ticket_code)

@section('content')
@php
    $isAdmin = Auth::user()->isAdmin();
    $isBooker = Auth::user()->isBooker() && !$isAdmin;
    $isLunas = $ticket->status === 'Lunas';
    
    $isBookerLunas = $isBooker && $isLunas;
    $isDataLocked = $isBookerLunas;
    
    $isBookerUnpaid = $isBooker && $ticket->status === 'Belum Bayar';
@endphp

<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('tickets.index') }}" class="text-xs font-medium text-sky-400 hover:text-sky-300 inline-flex items-center gap-1.5 mb-2">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Tiket
            </a>
            <h1 class="font-display text-2xl sm:text-3xl font-bold text-white">Edit Histori Tiket</h1>
            <p class="text-slate-400 text-sm mt-1">Perbarui data tiket <span class="font-mono text-sky-400 font-semibold">{{ $ticket->ticket_code }}</span></p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('tickets.pdf', $ticket->id) }}" target="_blank" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-sky-600 hover:bg-sky-500 shadow-lg shadow-sky-500/20 transition-all flex items-center gap-2">
                <i class="fa-solid fa-file-pdf text-sm"></i> Cetak / Download Boarding Pass PDF
            </a>
        </div>
    </div>

    <div class="glass-card p-6 sm:p-8 rounded-2xl shadow-2xl">
        @if($isBookerLunas)
            <div class="mb-6 p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs flex items-center gap-3">
                <i class="fa-solid fa-lock text-xl text-amber-400 shrink-0"></i>
                <div>
                    <span class="font-bold block text-sm">Tiket Berstatus Lunas — Mode Pembatasan Akses (Booker & Payer)</span>
                    <span>Data rute, penumpang, dan biaya tiket telah dikunci karena pembayaran sudah <strong>Lunas</strong>. Sebagai Booker & Payer, Anda diperbolehkan mengedit <strong>Tanggal Pembayaran</strong> atau mengubah status menjadi <strong>Dibatalkan</strong>.</span>
                </div>
            </div>
        @endif

        <form action="{{ route('tickets.update', $ticket->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            @if($isDataLocked)
                <input type="hidden" name="ticket_code" value="{{ old('ticket_code', $ticket->ticket_code) }}">
                <input type="hidden" name="ticket_date" value="{{ old('ticket_date', $ticket->ticket_date->format('Y-m-d')) }}">
                <input type="hidden" name="origin" value="{{ old('origin', $ticket->origin) }}">
                <input type="hidden" name="destination" value="{{ old('destination', $ticket->destination) }}">
                <input type="hidden" name="transport_type" value="{{ old('transport_type', $ticket->transport_type) }}">
                @foreach($ticket->passengers_list as $pName)
                    <input type="hidden" name="passenger_names[]" value="{{ $pName }}">
                @endforeach
                <input type="hidden" name="amount" value="{{ old('amount', $ticket->amount) }}">
                <input type="hidden" name="booked_by" value="{{ old('booked_by', $ticket->booked_by) }}">
                <input type="hidden" name="booked_by_user_id" value="{{ old('booked_by_user_id', $ticket->booked_by_user_id) }}">
            @endif

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
                        <input type="date" id="ticket_date" name="ticket_date" value="{{ old('ticket_date', $ticket->ticket_date ? $ticket->ticket_date->format('Y-m-d') : '') }}" {{ $isDataLocked ? 'disabled' : 'required' }} class="w-full glass-input rounded-xl px-4 py-2.5 text-sm bg-slate-900 @error('ticket_date') border-rose-500 @enderror">
                        @error('ticket_date')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="ticket_code" class="block text-xs font-medium text-slate-300 mb-1.5">
                            Kode Tiket / Ref Booking <span class="text-rose-400">*</span>
                        </label>
                        <input type="text" id="ticket_code" name="ticket_code" value="{{ old('ticket_code', $ticket->ticket_code) }}" {{ $isDataLocked ? 'disabled' : 'required' }} class="w-full glass-input rounded-xl px-4 py-2.5 text-sm font-mono @error('ticket_code') border-rose-500 @enderror">
                        @error('ticket_code')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="origin" class="block text-xs font-medium text-slate-300 mb-1.5">
                            Dari (Lokasi Keberangkatan) <span class="text-rose-400">*</span>
                        </label>
                        <input type="text" id="origin" name="origin" value="{{ old('origin', $ticket->origin) }}" {{ $isDataLocked ? 'disabled' : 'required' }} class="w-full glass-input rounded-xl px-4 py-2.5 text-sm @error('origin') border-rose-500 @enderror">
                        @error('origin')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="destination" class="block text-xs font-medium text-slate-300 mb-1.5">
                            Ke (Lokasi Tujuan) <span class="text-rose-400">*</span>
                        </label>
                        <input type="text" id="destination" name="destination" value="{{ old('destination', $ticket->destination) }}" {{ $isDataLocked ? 'disabled' : 'required' }} class="w-full glass-input rounded-xl px-4 py-2.5 text-sm @error('destination') border-rose-500 @enderror">
                        @error('destination')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="transport_type" class="block text-xs font-medium text-slate-300 mb-1.5">
                            Jenis Transportasi <span class="text-rose-400">*</span>
                        </label>
                        <select id="transport_type" name="transport_type" {{ $isDataLocked ? 'disabled' : 'required' }} class="w-full glass-input rounded-xl px-4 py-2.5 text-sm bg-slate-900 @error('transport_type') border-rose-500 @enderror">
                            @foreach($transportOptions as $option)
                                <option value="{{ $option }}" {{ old('transport_type', $ticket->transport_type) == $option ? 'selected' : '' }}>{{ $option }}</option>
                            @endforeach
                        </select>
                        @error('transport_type')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <hr class="border-slate-800/80">

            <!-- Dynamic Multiple Passengers Input -->
            <div x-data="{ passengers: {{ json_encode(old('passenger_names', $ticket->passengers_list)) }} }">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-sky-400 uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-users"></i> Daftar Nama Penumpang (<span x-text="passengers.length"></span> Orang)
                    </h3>
                    @if(!$isDataLocked)
                        <button type="button" @click="passengers.push('')" class="text-xs font-semibold text-sky-400 hover:text-sky-300 px-3 py-1.5 rounded-lg bg-sky-500/10 border border-sky-500/30 transition-all flex items-center gap-1.5">
                            <i class="fa-solid fa-user-plus"></i> Tambah Penumpang
                        </button>
                    @endif
                </div>

                <div class="space-y-3">
                    <template x-for="(passenger, index) in passengers" :key="index">
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs font-mono font-bold" x-text="(index + 1) + '.'"></div>
                                <input type="text" :name="'passenger_names[' + index + ']'" x-model="passengers[index]" placeholder="Nama Penumpang (Lengkap)" {{ $isDataLocked ? 'disabled' : 'required' }} class="w-full glass-input rounded-xl pl-9 pr-4 py-2.5 text-sm placeholder-slate-600">
                            </div>
                            @if(!$isDataLocked)
                                <button type="button" @click="passengers.splice(index, 1)" x-show="passengers.length > 1" class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-400 hover:bg-rose-500 hover:text-white flex items-center justify-center transition-colors shadow-sm" title="Hapus Penumpang Ini">
                                    <i class="fa-solid fa-trash-can text-sm"></i>
                                </button>
                            @endif
                        </div>
                    </template>
                </div>
                @error('passenger_names')
                    <p class="text-rose-400 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>

            <hr class="border-slate-800/80">

            <!-- Section 3: Pemesanan & Pembayaran -->
            <div>
                <h3 class="text-sm font-semibold text-emerald-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-credit-card"></i> Detail Pemesan & Pembayaran Oleh
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Booker account info in edit (Locked for Non-Admin) -->
                    @if(!$isAdmin)
                        <div class="md:col-span-2 bg-sky-500/10 border border-sky-500/30 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-inner">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-sky-500/20 text-sky-400 flex items-center justify-center font-bold text-sm shrink-0">
                                    <i class="fa-solid fa-user-check"></i>
                                </div>
                                <div>
                                    <div class="text-xs text-sky-400 font-semibold uppercase tracking-wider">Pemesan Tiket</div>
                                    <div class="text-sm font-bold text-white flex items-center gap-2">
                                        {{ $ticket->booked_by ?: Auth::user()->name }}
                                        @if($ticket->bookerUser)
                                            <span class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-sky-400/20 text-sky-300 border border-sky-400/30">
                                                ID: #{{ $ticket->bookerUser->id }} • {{ ucfirst($ticket->bookerUser->role) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="booked_by" value="{{ old('booked_by', $ticket->booked_by ?: Auth::user()->name) }}">
                            <input type="hidden" name="booked_by_user_id" value="{{ old('booked_by_user_id', $ticket->booked_by_user_id ?: Auth::id()) }}">
                        </div>
                    @else
                        <div>
                            <label for="booked_by" class="block text-xs font-medium text-slate-300 mb-1.5">
                                Pemesan Tiket <span class="text-rose-400">*</span>
                            </label>
                            <input type="text" id="booked_by" name="booked_by" value="{{ old('booked_by', $ticket->booked_by) }}" required class="w-full glass-input rounded-xl px-4 py-2.5 text-sm @error('booked_by') border-rose-500 @enderror">
                            @error('booked_by')
                                <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="booked_by_user_id" class="block text-xs font-medium text-slate-300 mb-1.5">
                                Linkkan dengan Akun Pemesan (Sistem)
                            </label>
                            <select id="booked_by_user_id" name="booked_by_user_id" class="w-full glass-input rounded-xl px-4 py-2.5 text-sm bg-slate-900">
                                <option value="">-- Pilih Akun User Pemesan --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('booked_by_user_id', $ticket->booked_by_user_id) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ ucfirst($user->role) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- Payer & Payment Date info logic -->
                    @if($isBooker)
                        <!-- Booker & Payer Auto-Linked Card & Editable Payment Date -->
                        <div class="md:col-span-2 bg-sky-500/10 border border-sky-500/30 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-inner">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-sky-500/20 text-sky-400 flex items-center justify-center font-bold text-sm shrink-0">
                                    <i class="fa-solid fa-credit-card"></i>
                                </div>
                                <div>
                                    <div class="text-xs text-sky-400 font-semibold uppercase tracking-wider">Pembayaran Oleh (Booker & Payer)</div>
                                    <div class="text-sm font-bold text-white flex items-center gap-2">
                                        {{ $ticket->paid_by && $ticket->paid_by !== '-' ? $ticket->paid_by : Auth::user()->name }}
                                        <span class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-sky-400/20 text-sky-300 border border-sky-400/30">
                                            Booker & Payer
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="paid_by" value="{{ $ticket->paid_by && $ticket->paid_by !== '-' ? $ticket->paid_by : Auth::user()->name }}">
                            <input type="hidden" name="paid_by_user_id" value="{{ $ticket->paid_by_user_id ?: Auth::id() }}">
                        </div>

                        <div class="md:col-span-2">
                            <label for="payment_date" class="block text-xs font-medium text-slate-300 mb-1.5">
                                Tanggal Pembayaran <span class="text-slate-400">(Wajib diisi jika status Lunas)</span>
                            </label>
                            <input type="date" id="payment_date" name="payment_date" value="{{ old('payment_date', $ticket->payment_date ? $ticket->payment_date->format('Y-m-d') : '') }}" class="w-full glass-input rounded-xl px-4 py-2.5 text-sm bg-slate-900 @error('payment_date') border-rose-500 @enderror">
                            @error('payment_date')
                                <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @else
                        <div>
                            <label for="paid_by" class="block text-xs font-medium text-slate-300 mb-1.5">
                                Pembayaran Oleh <span class="text-rose-400">*</span>
                            </label>
                            <input type="text" id="paid_by" name="paid_by" value="{{ old('paid_by', $ticket->paid_by) }}" required class="w-full glass-input rounded-xl px-4 py-2.5 text-sm @error('paid_by') border-rose-500 @enderror">
                            @error('paid_by')
                                <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="paid_by_user_id" class="block text-xs font-medium text-slate-300 mb-1.5">
                                Linkkan dengan Akun Pembayar (Sistem)
                            </label>
                            <select id="paid_by_user_id" name="paid_by_user_id" class="w-full glass-input rounded-xl px-4 py-2.5 text-sm bg-slate-900">
                                <option value="">-- Pilih Akun User Pembayar --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('paid_by_user_id', $ticket->paid_by_user_id) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ ucfirst($user->role) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label for="payment_date" class="block text-xs font-medium text-slate-300 mb-1.5">
                                Tanggal Pembayaran
                            </label>
                            <input type="date" id="payment_date" name="payment_date" value="{{ old('payment_date', $ticket->payment_date ? $ticket->payment_date->format('Y-m-d') : '') }}" class="w-full glass-input rounded-xl px-4 py-2.5 text-sm bg-slate-900 @error('payment_date') border-rose-500 @enderror">
                            @error('payment_date')
                                <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <div>
                        <label for="amount" class="block text-xs font-medium text-slate-300 mb-1.5">
                            Harga / Biaya Tiket (IDR) <span class="text-rose-400">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs font-mono font-bold">
                                Rp
                            </div>
                            <input type="number" step="any" min="0" id="amount" name="amount" value="{{ old('amount', $ticket->amount) }}" {{ $isDataLocked ? 'disabled' : 'required' }} class="w-full glass-input rounded-xl pl-10 pr-4 py-2.5 text-sm font-mono @error('amount') border-rose-500 @enderror">
                        </div>
                        @error('amount')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-xs font-medium text-slate-300 mb-1.5">
                            Status Pembayaran <span class="text-rose-400">*</span>
                        </label>
                        @if($isBooker)
                            <select id="status" name="status" required class="w-full glass-input rounded-xl px-4 py-2.5 text-sm bg-slate-900 @error('status') border-rose-500 @enderror">
                                @if($ticket->status === 'Lunas')
                                    <option value="Lunas" {{ old('status', $ticket->status) == 'Lunas' ? 'selected' : '' }}>Lunas (Status Saat Ini)</option>
                                    <option value="Dibatalkan" {{ old('status', $ticket->status) == 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                                @else
                                    <option value="Belum Bayar" {{ old('status', $ticket->status) == 'Belum Bayar' ? 'selected' : '' }}>Belum Bayar</option>
                                    <option value="Lunas" {{ old('status', $ticket->status) == 'Lunas' ? 'selected' : '' }}>Lunas (Konfirmasi Pembayaran)</option>
                                    <option value="Dibatalkan" {{ old('status', $ticket->status) == 'Dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                                @endif
                            </select>
                        @else
                            <select id="status" name="status" required class="w-full glass-input rounded-xl px-4 py-2.5 text-sm bg-slate-900 @error('status') border-rose-500 @enderror">
                                @foreach($statusOptions as $optStatus)
                                    <option value="{{ $optStatus }}" {{ old('status', $ticket->status) == $optStatus ? 'selected' : '' }}>{{ $optStatus }}</option>
                                @endforeach
                            </select>
                        @endif
                        @error('status')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="attachment" class="block text-xs font-medium text-slate-300 mb-1.5">
                            Ganti File Bukti / Nota Tiket <span class="text-slate-500">(Opsional)</span>
                        </label>
                        <input type="file" id="attachment" name="attachment" accept=".pdf,.jpg,.jpeg,.png" {{ $isDataLocked ? 'disabled' : '' }} class="w-full glass-input rounded-xl px-3 py-2 text-xs bg-slate-900 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-500/20 file:text-sky-300 hover:file:bg-sky-500/30">
                        @if($ticket->attachment_path)
                            <p class="text-xs text-sky-400 mt-1">
                                <i class="fa-solid fa-paperclip mr-1"></i> File saat ini: <a href="{{ asset('storage/' . $ticket->attachment_path) }}" target="_blank" class="underline">Lihat Lampiran</a>
                            </p>
                        @endif
                        @error('attachment')
                            <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <hr class="border-slate-800/80">

            <div>
                <label for="notes" class="block text-xs font-medium text-slate-300 mb-1.5">
                    Catatan / Keterangan Tambahan
                </label>
                <textarea id="notes" name="notes" rows="3" {{ $isDataLocked ? 'disabled' : '' }} class="w-full glass-input rounded-xl p-4 text-sm">{{ old('notes', $ticket->notes) }}</textarea>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-slate-800/80">
                <a href="{{ route('tickets.pdf', $ticket->id) }}" target="_blank" class="w-full sm:w-auto px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-200 bg-slate-800 hover:bg-slate-700 border border-slate-700 transition-colors flex items-center justify-center gap-2">
                    <i class="fa-solid fa-file-pdf text-sky-400"></i> Cetak / Download Boarding Pass PDF
                </a>

                <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                    <a href="{{ route('tickets.index') }}" class="px-5 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700 transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-400 hover:to-orange-500 shadow-lg shadow-amber-500/25 transition-all">
                        <i class="fa-solid fa-pen-to-square mr-2"></i> Perbarui Tiket
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
