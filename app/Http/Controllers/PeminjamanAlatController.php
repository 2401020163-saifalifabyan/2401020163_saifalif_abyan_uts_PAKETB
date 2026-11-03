<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PDO;
use Throwable;

class PeminjamanAlatController extends Controller
{
    // ==========================================
    // READ: MENAMPILKAN DATA, PENCARIAN, FILTER
    // ==========================================

    public function index(Request $request)
    {
        $q = $this->bersihkanTeks($request->query('q', ''));

        $alatId = $this->bersihkanTeks(
            $request->query('alat_id', '')
        );

        if ($alatId !== '' && !ctype_digit($alatId)) {
            $alatId = '';
        }

        try {
            $pdo = DB::connection()->getPdo();

            // Mengambil daftar alat
            $stmtAlat = $pdo->prepare(
                "SELECT id, nama_alat FROM alat ORDER BY nama_alat ASC"
            );

            $stmtAlat->execute();

            $alatList = $stmtAlat->fetchAll(PDO::FETCH_OBJ);

            // Query untuk mengambil data peminjaman
            $sql = "
                SELECT
                    p.id,
                    p.kode_peminjaman,
                    p.nama_peminjam,
                    p.nomor_identitas,
                    p.jumlah_alat,
                    p.alat_id,
                    a.nama_alat
                FROM peminjaman_alat AS p
                INNER JOIN alat AS a ON p.alat_id = a.id
                WHERE 1=1
            ";

            $params = [];

            // Pencarian berdasarkan kode, nama, dan identitas
            if ($q !== '') {
                $sql .= "
                    AND (
                        p.kode_peminjaman LIKE :kode
                        OR p.nama_peminjam LIKE :nama
                        OR p.nomor_identitas LIKE :identitas
                    )
                ";

                $keyword = '%' . $q . '%';

                $params['kode'] = $keyword;
                $params['nama'] = $keyword;
                $params['identitas'] = $keyword;
            }

            // Filter berdasarkan alat
            if ($alatId !== '') {
                $sql .= " AND p.alat_id = :alat_id";

                $params['alat_id'] = (int) $alatId;
            }

            $sql .= " ORDER BY p.id DESC";

            $stmt = $pdo->prepare($sql);

            $stmt->execute($params);

            $data = $stmt->fetchAll(PDO::FETCH_OBJ);

            return view('peminjaman.index', compact(
                'data',
                'alatList',
                'q',
                'alatId'
            ));

        } catch (Throwable $e) {

            report($e);

            return view('peminjaman.index', [
                'data' => [],
                'alatList' => [],
                'q' => $q,
                'alatId' => $alatId,
                'errorDb' => 'Data gagal dimuat. Periksa koneksi database.',
            ]);
        }
    }


    // ==========================================
    // CREATE: MENAMBAHKAN DATA PEMINJAMAN
    // ==========================================

    public function store(Request $request)
    {
        $data = $this->validasiInput($request);

        try {
            $pdo = DB::connection()->getPdo();

            // Memeriksa kode peminjaman duplikat
            if ($this->cekDuplikat(
                $pdo,
                'kode_peminjaman',
                $data['kode_peminjaman']
            )) {
                return back()
                    ->withErrors([
                        'kode_peminjaman' =>
                        'Kode peminjaman sudah digunakan.',
                    ])
                    ->withInput();
            }

            // Memeriksa nomor identitas duplikat
            if ($this->cekDuplikat(
                $pdo,
                'nomor_identitas',
                $data['nomor_identitas']
            )) {
                return back()
                    ->withErrors([
                        'nomor_identitas' =>
                        'Nomor identitas sudah digunakan.',
                    ])
                    ->withInput();
            }

            // Memastikan alat tersedia
            if (!$this->alatTersedia($pdo, $data['alat_id'])) {
                return back()
                    ->withErrors([
                        'alat_id' => 'Alat yang dipilih tidak tersedia.',
                    ])
                    ->withInput();
            }

            // Query INSERT menggunakan prepared statement
            $sql = "
                INSERT INTO peminjaman_alat
                (
                    kode_peminjaman,
                    nama_peminjam,
                    nomor_identitas,
                    jumlah_alat,
                    alat_id,
                    created_at,
                    updated_at
                )
                VALUES
                (
                    :kode,
                    :nama,
                    :identitas,
                    :jumlah,
                    :alat,
                    NOW(),
                    NOW()
                )
            ";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                'kode' => $data['kode_peminjaman'],
                'nama' => $data['nama_peminjam'],
                'identitas' => $data['nomor_identitas'],
                'jumlah' => $data['jumlah_alat'],
                'alat' => $data['alat_id'],
            ]);

            return redirect()
                ->route('peminjaman.index')
                ->with(
                    'success',
                    'Data peminjaman berhasil ditambahkan.'
                );

        } catch (Throwable $e) {

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Data gagal disimpan. Periksa kembali data dan koneksi database.'
                );
        }
    }


    // ==========================================
    // EDIT: MENAMPILKAN FORM EDIT
    // ==========================================

    public function edit(int $id)
    {
        try {
            $pdo = DB::connection()->getPdo();

            // Mengambil data yang akan diedit
            $stmt = $pdo->prepare(
                "SELECT * FROM peminjaman_alat WHERE id = :id"
            );

            $stmt->execute([
                'id' => $id,
            ]);

            $item = $stmt->fetch(PDO::FETCH_OBJ);

            // Mengambil daftar alat
            $stmtAlat = $pdo->prepare(
                "SELECT id, nama_alat FROM alat ORDER BY nama_alat ASC"
            );

            $stmtAlat->execute();

            $alatList = $stmtAlat->fetchAll(PDO::FETCH_OBJ);

        } catch (Throwable $e) {

            report($e);

            return redirect()
                ->route('peminjaman.index')
                ->with('error', 'Form edit gagal dibuka.');
        }

        if (!$item) {
            abort(404);
        }

        return view('peminjaman.edit', compact(
            'item',
            'alatList'
        ));
    }


    // ==========================================
    // UPDATE: MEMPERBARUI DATA
    // ==========================================

    public function update(Request $request, int $id)
    {
        $data = $this->validasiInput($request);

        try {
            $pdo = DB::connection()->getPdo();

            // Memeriksa apakah data tersedia
            $stmtCek = $pdo->prepare(
                "SELECT id FROM peminjaman_alat WHERE id = :id"
            );

            $stmtCek->execute([
                'id' => $id,
            ]);

            if (!$stmtCek->fetch(PDO::FETCH_ASSOC)) {
                return redirect()
                    ->route('peminjaman.index')
                    ->with(
                        'error',
                        'Data peminjaman tidak ditemukan.'
                    );
            }

            // Memeriksa kode milik data lain
            if ($this->cekDuplikat(
                $pdo,
                'kode_peminjaman',
                $data['kode_peminjaman'],
                $id
            )) {
                return back()
                    ->withErrors([
                        'kode_peminjaman' =>
                        'Kode peminjaman sudah digunakan data lain.',
                    ])
                    ->withInput();
            }

            // Memeriksa identitas milik data lain
            if ($this->cekDuplikat(
                $pdo,
                'nomor_identitas',
                $data['nomor_identitas'],
                $id
            )) {
                return back()
                    ->withErrors([
                        'nomor_identitas' =>
                        'Nomor identitas sudah digunakan data lain.',
                    ])
                    ->withInput();
            }

            // Memastikan alat tersedia
            if (!$this->alatTersedia($pdo, $data['alat_id'])) {
                return back()
                    ->withErrors([
                        'alat_id' => 'Alat yang dipilih tidak tersedia.',
                    ])
                    ->withInput();
            }

            // Query UPDATE menggunakan prepared statement
            $sql = "
                UPDATE peminjaman_alat
                SET
                    kode_peminjaman = :kode,
                    nama_peminjam = :nama,
                    nomor_identitas = :identitas,
                    jumlah_alat = :jumlah,
                    alat_id = :alat,
                    updated_at = NOW()
                WHERE id = :id
            ";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                'kode' => $data['kode_peminjaman'],
                'nama' => $data['nama_peminjam'],
                'identitas' => $data['nomor_identitas'],
                'jumlah' => $data['jumlah_alat'],
                'alat' => $data['alat_id'],
                'id' => $id,
            ]);

            return redirect()
                ->route('peminjaman.index')
                ->with(
                    'success',
                    'Data peminjaman berhasil diperbarui.'
                );

        } catch (Throwable $e) {

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Data gagal diperbarui. Periksa kembali data dan koneksi database.'
                );
        }
    }


    // ==========================================
    // DELETE: MENGHAPUS DATA
    // ==========================================

    public function destroy(int $id)
    {
        try {
            $pdo = DB::connection()->getPdo();

            $stmt = $pdo->prepare(
                "DELETE FROM peminjaman_alat WHERE id = :id"
            );

            $stmt->execute([
                'id' => $id,
            ]);

            if ($stmt->rowCount() === 0) {
                return back()->with(
                    'error',
                    'Data tidak ditemukan.'
                );
            }

            return redirect()
                ->route('peminjaman.index')
                ->with(
                    'success',
                    'Data peminjaman berhasil dihapus.'
                );

        } catch (Throwable $e) {

            report($e);

            return back()->with(
                'error',
                'Data gagal dihapus.'
            );
        }
    }


    // ==========================================
    // SANITASI INPUT
    // ==========================================

    private function bersihkanTeks($value): string
    {
        if (!is_scalar($value)) {
            return '';
        }

        return trim(strip_tags((string) $value));
    }


    // ==========================================
    // VALIDASI INPUT
    // ==========================================

    private function validasiInput(Request $request): array
    {
        $data = [

            'kode_peminjaman' => $this->bersihkanTeks(
                $request->input('kode_peminjaman', '')
            ),

            'nama_peminjam' => $this->bersihkanTeks(
                $request->input('nama_peminjam', '')
            ),

            'nomor_identitas' => $this->bersihkanTeks(
                $request->input('nomor_identitas', '')
            ),

            'jumlah_alat' => $this->bersihkanTeks(
                $request->input('jumlah_alat', '')
            ),

            'alat_id' => $this->bersihkanTeks(
                $request->input('alat_id', '')
            ),
        ];

        // Menyimpan input yang sudah dibersihkan
        $request->merge($data);

        $rules = [

            'kode_peminjaman' => [
                'required',
                'regex:/^[A-Za-z0-9]{10}$/',
            ],

            'nama_peminjam' => [
                'required',
                'string',
                'min:3',
                'max:100',
            ],

            'nomor_identitas' => [
                'required',
                'string',
                'min:5',
                'max:30',
            ],

            'jumlah_alat' => [
                'required',
                'integer',
                'between:1,10',
            ],

            'alat_id' => [
                'required',
                'integer',
                'min:1',
            ],
        ];

        $messages = [

            'kode_peminjaman.required' =>
            'Kode peminjaman wajib diisi.',

            'kode_peminjaman.regex' =>
            'Kode harus tepat 10 karakter alfanumerik.',

            'nama_peminjam.required' =>
            'Nama peminjam wajib diisi.',

            'nama_peminjam.min' =>
            'Nama minimal 3 karakter.',

            'nama_peminjam.max' =>
            'Nama maksimal 100 karakter.',

            'nomor_identitas.required' =>
            'Nomor identitas wajib diisi.',

            'nomor_identitas.min' =>
            'Nomor identitas minimal 5 karakter.',

            'nomor_identitas.max' =>
            'Nomor identitas maksimal 30 karakter.',

            'jumlah_alat.integer' =>
            'Jumlah alat harus berupa bilangan bulat.',

            'jumlah_alat.between' =>
            'Jumlah alat harus antara 1 sampai 10.',

            'alat_id.required' =>
            'Silakan pilih alat.',

            'alat_id.integer' =>
            'Pilihan alat tidak valid.',
        ];

        return Validator::make(
            $data,
            $rules,
            $messages
        )->validate();
    }


    // ==========================================
    // MEMERIKSA DUPLIKAT
    // ==========================================

    private function cekDuplikat(
        PDO $pdo,
        string $kolom,
        string $nilai,
        ?int $id = null
    ): bool {

        // Kolom yang diperbolehkan
        $kolomDiizinkan = [
            'kode_peminjaman',
            'nomor_identitas',
        ];

        if (!in_array($kolom, $kolomDiizinkan, true)) {
            throw new \InvalidArgumentException(
                'Kolom tidak diizinkan.'
            );
        }

        $sql = "
            SELECT COUNT(*)
            FROM peminjaman_alat
            WHERE {$kolom} = :nilai
        ";

        $params = [
            'nilai' => $nilai,
        ];

        // Mengecualikan ID data yang sedang diedit
        if ($id !== null) {
            $sql .= " AND id <> :id";

            $params['id'] = $id;
        }

        $stmt = $pdo->prepare($sql);

        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }


    // ==========================================
    // MEMERIKSA KETERSEDIAAN ALAT
    // ==========================================

    private function alatTersedia(
        PDO $pdo,
        string $alatId
    ): bool {

        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM alat WHERE id = :id"
        );

        $stmt->execute([
            'id' => $alatId,
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }
}
