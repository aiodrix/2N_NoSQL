DecenPHPv1.2.3.2

Our project aims to build simple distributed and decentralized systems, as well as to create static pages..

Main features: Each server displays all the files it hosts in files.php (showing a list of links) or files_json.php (showing a JSON text). This enables decentralized searching by checking if the user input matches the name of a file hosted on a server using search.php or search_json.php.

The receiver.php page can receive files from any user via a POST request. The file will be saved in the 'files' directory and renamed with the file's SHA-256 hash (plus the original extension). To send a file to a server, the user can use sender.php, sender_multiple.php or sender_multiple_list.php.

If the user wants to download all files from a server, they just need to use download_links.php and insert the desired URL using GET (for example, download_links.php?url=https://testsitename.com/files.php).

Within the 'html' directory there will be subdirectories categories with files organized inside (such as '1.html', '2.html'). This allows the user to find a category even if the server site is completely static. The html_creator_2.php tool assists in creating static pages and categories.php show all categories.

---------------------
