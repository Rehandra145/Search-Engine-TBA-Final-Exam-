<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Search Engine</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #ffffff; /* Mengubah background menjadi putih */
            color: #333333; /* Mengubah warna teks menjadi gelap */
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center; /* Menempatkan elemen di tengah */
        }
        h1 {
            color: #8a2be2;
            margin-bottom: 20px;
        }
        #searchForm {
            display: flex;
            gap: 10px;
        }
        #keyword {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #8a2be2;
            border-radius: 5px;
            background-color: #f5f5f5; /* Latar belakang input */
            color: #333333; /* Warna teks input */
            outline: none;
            width: 300px;
        }
        #keyword:focus {
            border-color: #ff69b4; /* Warna border saat fokus */
        }
        button {
            padding: 10px 20px;
            font-size: 16px;
            color: #f0f0f0;
            background-color: #8a2be2;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #ff69b4; /* Warna tombol saat hover */
        }
        .suggestions {
            border: 1px solid #8a2be2;
            background-color: #f5f5f5; /* Latar belakang suggestions */
            color: #333333; /* Warna teks suggestions */
            max-height: 150px;
            overflow-y: auto;
            padding: 5px;
            margin-top: 5px;
            width: 320px;
            border-radius: 5px;
        }
        .suggestion-item {
            padding: 5px;
            cursor: pointer;
        }
        .suggestion-item:hover {
            background-color: #8a2be2; /* Warna background saat hover */
            color: #ffffff; /* Warna teks saat hover */
        }
        .results {
            margin-top: 20px;
            width: 100%; /* Menjaga agar hasil pencarian lebar 100% */
            max-width: 600px; /* Membatasi lebar maksimum hasil */
        }
        .result-item {
            background-color: #f0f0f0; /* Latar belakang hasil */
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #333333; /* Warna teks hasil */
            border: 1px solid #8a2be2; /* Border hasil */
        }
        .result-item h2 {
            color: #8a2be2; /* Warna judul hasil */
        }
        .result-item img,
        .result-item video {
            margin-top: 10px;
            border-radius: 5px;
            max-width: 100%;
        }
    </style>
</head>
<body>
    <h1>Cari apa aja...</h1>
    <form id="searchForm">
        <input type="text" id="keyword" placeholder="Masukkan kata kunci" required autocomplete="off">
        <button type="submit">Cari</button>
    </form>
    <div class="suggestions" id="suggestions"></div>
    <div class="results" id="results"></div>

    <script>
        const searchForm = document.getElementById('searchForm');
        const keywordInput = document.getElementById('keyword');
        const suggestionsDiv = document.getElementById('suggestions');
        const resultsDiv = document.getElementById('results');

        keywordInput.addEventListener('input', function() {
            const keyword = keywordInput.value;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch('/search', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ keyword: keyword })
            })
            .then(response => response.json())
            .then(data => {
                suggestionsDiv.innerHTML = '';
                if (data.suggestions) {
                    const { kata, kalimat } = data.suggestions;

                    kata.forEach(item => {
                        const itemDiv = document.createElement('div');
                        itemDiv.classList.add('suggestion-item');
                        itemDiv.textContent = item;
                        itemDiv.onclick = () => {
                            keywordInput.value = item;
                            suggestionsDiv.innerHTML = '';
                        };
                        suggestionsDiv.appendChild(itemDiv);
                    });

                    kalimat.forEach(sentence => {
                        const sentenceDiv = document.createElement('div');
                        sentenceDiv.classList.add('suggestion-item');
                        sentenceDiv.textContent = sentence;
                        sentenceDiv.onclick = () => {
                            keywordInput.value = sentence;
                            suggestionsDiv.innerHTML = '';
                        };
                        suggestionsDiv.appendChild(sentenceDiv);
                    });
                }
            })
            .catch(error => console.error('Error:', error));
        });

        searchForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const keyword = keywordInput.value;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            suggestionsDiv.innerHTML = '';
            resultsDiv.innerHTML = '';

            fetch('/search', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ keyword: keyword })
            })
            .then(response => response.json())
            .then(data => {
                if (data.results && data.results.length > 0) {
                    data.results.forEach(item => {
                        const itemDiv = document.createElement('div');
                        itemDiv.classList.add('result-item');
                        itemDiv.innerHTML = `
                            <h2>${item.title}</h2>
                            <p>${item.description}</p>
                            <img src="/storage/UAS_TBA/${item.foto}" alt="${item.title}" width="200" height="200">
                            <video controls width="200" height="200">
                                <source src="/storage/UAS_TBA/${item.video}" type="video/mp4">
                                Browser Anda tidak mendukung video.
                            </video>
                        `;
                        resultsDiv.appendChild(itemDiv);
                    });
                } else {
                    resultsDiv.innerHTML = '<p>Tidak ada hasil yang ditemukan.</p>';
                }
            })
            .catch(error => console.error('Error:', error));
        });
    </script>
</body>
</html>
