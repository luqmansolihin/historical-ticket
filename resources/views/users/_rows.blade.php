@forelse($users as $userItem)
    <tr @dblclick="window.loadSpaPage('{{ route('users.edit', $userItem->id) }}')"
        class="hover:bg-purple-50/70 cursor-pointer transition-colors group whitespace-nowrap border-b border-slate-200 select-none"
        title="Double klik untuk mengedit akun {{ $userItem->name }}">
        <!-- 1. Nama Pengguna -->
        <td class="py-0.5 px-2 whitespace-nowrap border-r border-slate-200">
            <div class="flex items-center gap-2">
                <span class="font-mono text-slate-500 text-[9px]">#{{ $userItem->id }}</span>
                <span class="font-semibold text-slate-900">{{ $userItem->name }}</span>
                @if(Auth::id() === $userItem->id)
                    <span class="px-1.5 py-0 text-[8.5px] font-mono rounded bg-purple-100 text-purple-800 border border-purple-300">Anda</span>
                @endif
            </div>
        </td>

        <!-- 2. Email -->
        <td class="py-0.5 px-2 whitespace-nowrap font-mono text-slate-700 border-r border-slate-200">
            {{ $userItem->email }}
        </td>

        <!-- 3. Role Akses -->
        <td class="py-0.5 px-2 whitespace-nowrap border-r border-slate-200">
            <span class="px-1.5 py-0 text-[8.5px] font-semibold rounded-full border inline-flex items-center gap-1 {{ $userItem->role_badge_class }}">
                @if($userItem->isAdmin())
                    <i class="fa-solid fa-shield-halved text-amber-600 text-[8px]"></i>
                @elseif($userItem->isFinance())
                    <i class="fa-solid fa-user-check text-sky-600 text-[8px]"></i>
                @else
                    <i class="fa-solid fa-user text-slate-500 text-[8px]"></i>
                @endif
                {{ ucfirst($userItem->role) }}
            </span>
        </td>

        <!-- 4. Tanggal Terdaftar -->
        <td class="py-0.5 px-2 whitespace-nowrap font-mono text-slate-600">
            {{ $userItem->created_at ? $userItem->created_at->format('d/m/Y H:i') : '-' }}
        </td>
    </tr>
@empty
    <tr>
        <td colspan="4" class="py-12 text-center text-slate-500">
            <p class="text-xs font-medium text-slate-600">Tidak ada akun pengguna ditemukan</p>
        </td>
    </tr>
@endforelse
