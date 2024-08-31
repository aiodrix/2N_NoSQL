<?php include ("categories_json_creator.php"); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suggestions Input</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .input-container {
            position: relative;
            width: 300px;
            margin: 100px auto;
            text-align: center;
        }

        #search-text {
            font-size: 32px;
            margin-bottom: 20px;
        }

        #suggestion-input {
            width: 100%;
            font-size: 24px;
            padding: 10px;
            box-sizing: border-box;
        }

        #suggestions-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
            border: 1px solid #ccc;
            border-top: none;
            max-height: 200px;
            overflow-y: auto;
            position: absolute;
            width: 100%;
            box-sizing: border-box;
            background-color: white;
        }

        #suggestions-list li {
            padding: 10px;
            cursor: pointer;
        }

        #suggestions-list li:hover {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
<a href='categories_simple.php'>View Categories</a>
    <div class="input-container">
        <div id="search-text">Search</div>
        <input type="text" id="suggestion-input" placeholder="Type here..." autocomplete="off">
        <ul id="suggestions-list"></ul>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const input = document.getElementById('suggestion-input');
            const suggestionsList = document.getElementById('suggestions-list');

            // Fetch and load JSON data
            fetch('categories.json')
                .then(response => response.json())
                .then(data => {
                    input.addEventListener('input', () => {
                        const query = input.value.toLowerCase().trim();
                        if (query) {
                            suggestionsList.innerHTML = '';
                            const filteredSuggestions = data.filter(item => item.name.toLowerCase().includes(query));
                            filteredSuggestions.forEach(item => {
                                const suggestionItem = document.createElement('li');
                                suggestionItem.textContent = item.name;
                                suggestionItem.addEventListener('click', () => {
                                    window.location.href = item.link;
                                });
                                suggestionsList.appendChild(suggestionItem);
                            });
                        } else {
                            suggestionsList.innerHTML = '';
                        }
                    });
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                });
        });
    </script>
</body>
</html>