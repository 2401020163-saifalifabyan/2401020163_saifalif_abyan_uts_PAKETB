
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sistem Peminjaman Alat</title>
</head>
<body>

    <h1>Sistem Peminjaman Alat Laboratorium</h1>

    <p>Nama: Saif Alif Abyan</p>
    <p>NIM: 2401020163</p>

    <hr>

    <h2>Informasi</h2>

    @if(session('success'))
        <p>{{ session('success') }}</p>
    @endif

    @if(session('error'))
        <p>{{ session('error') }}</p>
    @endif

    @isset($errorDb)
        <p>{{ $errorDb }}</p>
    @endisset

    @if($errors->any())
        <h3>Terjadi kesalahan input:</h3>

        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <hr>

    <h2>Tambah Peminjaman Alat</h2>

    <form action="{{ route('peminjaman.store') }}" method="POST">

        @csrf

        <p>
            <label>Kode Peminjaman:</label><br>

            <input
                type="text"
                name="kode_peminjaman"
                maxlength="10"
                value="{{ old('kode_peminjaman') }}"
                required
            >

            @error('kode_peminjaman')
                <br><small>{{ $message }}</small>
            @enderror
        </p>

        <p>
            <label>Nama Peminjam:</label><br>

            <input
                type="text"
                name="nama_peminjam"
                maxlength="100"
                value="{{ old('nama_peminjam') }}"
                required
            >

            @error('nama_peminjam')
                <br><small>{{ $message }}</small>
            @enderror
        </p>

        <p>
            <label>Nomor Identitas:</label><br>

            <input
                type="text"
                name="nomor_identitas"
                maxlength="30"
                value="{{ old('nomor_identitas') }}"
                required
            >

            @error('nomor_identitas')
                <br><small>{{ $message }}</small>
            @enderror
        </p>

        <p>
            <label>Jumlah Alat:</label><br>

            <input
                type="number"
                name="jumlah_alat"
                min="1"
                max="10"
                value="{{ old('jumlah_alat') }}"
                required
            >

            @error('jumlah_alat')
                <br><small>{{ $message }}</small>
            @enderror
        </p>

        <p>
            <label>Pilih Alat:</label><br>

            <select name="alat_id" required>

                <option value="">-- Pilih Alat --</option>

                @foreach($alatList as $alat)

                    <option
                        value="{{ $alat->id }}"
                        {{ old('alat_id') == $alat->id ? 'selected' : '' }}
                    >
                        {{ $alat->nama_alat }}
                    </option>

                @endforeach

            </select>

            @error('alat_id')
                <br><small>{{ $message }}</small>
            @enderror
        </p>

        <button type="submit">Simpan Peminjaman</button>

    </form>

    <hr>

    <h2>Pencarian dan Filter Peminjaman</h2>

    <form action="{{ route('peminjaman.index') }}" method="GET">

        <p>
            <label>Cari Kode, Nama, atau Nomor Identitas:</label><br>

            <input
                type="text"
                name="q"
                value="{{ $q }}"
                placeholder="Masukkan kata pencarian"
            >
        </p>

        <p>
            <label>Filter Berdasarkan Alat:</label><br>

            <select name="alat_id">

                <option value="">-- Semua Alat --</option>

                @foreach($alatList as $alat)

                    <option
                        value="{{ $alat->id }}"
                        {{ $alatId == $alat->id ? 'selected' : '' }}
                    >
                        {{ $alat->nama_alat }}
                    </option>

                @endforeach

            </select>
        </p>

        <button type="submit">Cari dan Filter</button>

        <a href="{{ route('peminjaman.index') }}">Reset</a>

    </form>

    <hr>

    <h2>Daftar Data Peminjaman Alat</h2>

    <table border="1" cellpadding="8" cellspacing="0">

        <thead>
            <tr>
                <th>No.</th>
                <th>Kode Peminjaman</th>
                <th>Nama Peminjam</th>
                <th>Nomor Identitas</th>
                <th>Jumlah Alat</th>
                <th>Status Jumlah</th>
                <th>Nama Alat</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>

            @forelse($data as $index => $item)

                <tr>
                    <td>{{ $index + 1 }}</td>

                    <td>{{ $item->kode_peminjaman }}</td>

                    <td>{{ $item->nama_peminjam }}</td>

                    <td>{{ $item->nomor_identitas }}</td>

                    <td>{{ $item->jumlah_alat }}</td>

                    <td>
                        @if(
                            \App\Support\StatusHelper::statusJumlah(
                                (int) $item->jumlah_alat
                            ) === 'Banyak'
                        )
                            Banyak
                        @else
                            Sedikit
                        @endif
                    </td>

                    <td>{{ $item->nama_alat }}</td>

                    <td>
                        <a href="{{ route('peminjaman.edit', $item->id) }}">
                            Edit
                        </a>

                        |

                        <form
                            action="{{ route('peminjaman.destroy', $item->id) }}"
                            method="POST"
                        >
                            @csrf
                            @method('DELETE')

                            <button type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>

            @empty

                <tr>
                    <td colspan="8">
                        Belum ada data peminjaman.
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

</body>
</html>
