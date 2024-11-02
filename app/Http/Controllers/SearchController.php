<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        // Path
        $jsonPath = storage_path('app/public/UAS_TBA/database.json');
        // Cek path
        if (!file_exists($jsonPath)) {
            return response()->json(['error' => 'File JSON tidak ditemukan'], 404);
        }
        // Baca dan decode
        $json = file_get_contents($jsonPath);
        $data = json_decode($json, true);
        // Baca keyword dari request
        $keyword = strtolower(trim($request->input('keyword')));

        // Stemming keyword
        $stemmedKeyword = $this->stem($keyword);

        // Array untuk menyimpan hasil pencarian dan suggestions
        $results = [];
        $suggestions = [];

        foreach ($data as $letter => $entries) {
            foreach ($entries as $word => $details) {
                // Stemming word
                $stemmedWord = $this->stem($word);

                // Regex untuk mencocokkan kata
                if (preg_match("/^" . preg_quote($stemmedKeyword, '/') . "/i", $stemmedWord)) {
                    $suggestions['kata'][] = $word;

                    // Cari kalimat yang dimulai dengan kata
                    foreach ($details as $sentence => $content) {
                        if (preg_match("/^" . preg_quote($stemmedWord, '/') . "/i", $sentence)) {
                            $suggestions['kalimat'][] = $sentence;
                        }
                    }
                }
                // Jika ada teks yang cocok menggunakan regex
                foreach ($details as $sentence => $content) {
                    if (preg_match("/" . preg_quote($stemmedKeyword, '/') . "/i", strtolower($sentence))) {
                        $results[] = [
                            'title' => $sentence,
                            'description' => $content['teks'],
                            'foto' => $content['foto'],
                            'video' => $content['video']
                        ];
                    }
                }
            }
        }

        return response()->json([
            'results' => $results,
            'suggestions' => $suggestions
        ]);
    }

    // Fungsi Stemming
    private function stem($word)
    {
        // Daftar akhiran yang umum dalam bahasa Indonesia
        $suffixes = ['lah', 'kah', 'ku', 'mu', 'nya', 'an', 'es', 'ing', 'tion', 'ment', 's']; // Tambahkan lebih banyak sesuai kebutuhan
        foreach ($suffixes as $suffix) {
            if (substr($word, -strlen($suffix)) === $suffix) {
                return substr($word, 0, -strlen($suffix)); // Kembalikan kata yang sudah di-stem
            }
        }
        return $word; // Kembalikan kata asli jika tidak ada akhiran yang cocok
    }
}
