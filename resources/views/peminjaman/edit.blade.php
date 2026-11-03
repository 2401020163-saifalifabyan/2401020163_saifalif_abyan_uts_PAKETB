
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Peminjaman Alat</title>
</head>
<body>

    <h1>Edit Data Peminjaman Alat</h1>

    <p>Nama: Saif Alif Abyan</p>
    <p>NIM: 2401020163</p>

    <hr>

    @if(session('error'))
        <p>{{ session('error') }}</p>
    @endif

    @if($errors->any())
        <h3>Terjadi kesalahan input:</h3>

        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form
        action="{{ route('peminjaman.update', $item->id) }}"
        method="POST"
    >

        @csrf
        @method('PUT')

        <p>
            <label>Kode Peminjaman:</label><br>

            <input
                type="text"
                name="kode_peminjaman"
                maxlength="10"
                value="{{ old('kode_peminjaman', $item->kode_peminjaman) }}"
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
                value="{{ old('nama_peminjam', $item->nama_peminjam) }}"
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
                value="{{ old('nomor_identitas', $item->nomor_identitas) }}"
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
                value="{{ old('jumlah_alat', $item->jumlah_alat) }}"
                required
            >

            @error('jumlah_alat')
                <br><small>{{ $message }}</small>
            @enderror
        </p>

        <p>
            <label>Alat:</label><br>

            <select name="alat_id" required>

                @foreach($alatList as $alat)

                    <option
                        value="{{ $alat->id }}"
                        {{
                            old('alat_id', $item->alat_id) == $alat->id
                            ? 'selected'
                            : ''
                        }}
                    >
                        {{ $alat->nama_alat }}
                    </option>

                @endforeach

            </select>

            @error('alat_id')
                <br><small>{{ $message }}</small>
            @enderror
        </p>

        <button type="submit">Update Data</button>

    </form>

    <p>
        <a href="{{ route('peminjaman.index') }}">
            Kembali ke halaman utama
        </a>
    </p>

</body>
</html>
