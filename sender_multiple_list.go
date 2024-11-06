package main

import (
	"bytes"
	"fmt"
	"html/template"
	"io"
	"io/ioutil"
	"log"
	"mime/multipart"
	"net/http"
	"os"
	"path/filepath"
	"strings"
)

const uploadDir = "./files/"
const serversDir = "./servers/"

func main() {
	http.HandleFunc("/", uploadForm)
	http.HandleFunc("/upload", uploadFiles)

	log.Println("Server started at :8080")
	log.Fatal(http.ListenAndServe(":8080", nil))
}

func uploadForm(w http.ResponseWriter, r *http.Request) {
	tmpl := template.Must(template.New("form").Parse(formTemplate))
	tmpl.Execute(w, nil)
}

func uploadFiles(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}

	// Get URLs from the "servers" directory files
	urls, err := listServerFilesContents(serversDir)
	if err != nil {
		http.Error(w, "Failed to list server files", http.StatusInternalServerError)
		return
	}

	// Iterate through files in the "files" directory and upload them
	files, err := ioutil.ReadDir(uploadDir)
	if err != nil {
		http.Error(w, "Failed to read files directory", http.StatusInternalServerError)
		return
	}

	for _, file := range files {
		if file.IsDir() {
			continue
		}

		filePath := filepath.Join(uploadDir, file.Name())
		fileData, err := os.Open(filePath)
		if err != nil {
			fmt.Fprintf(w, "Error reading file %s: %v\n", file.Name(), err)
			continue
		}
		defer fileData.Close()

		for _, url := range urls {
			url = strings.TrimSpace(url)
			if url != "" {
				err := sendFile(fileData, file.Name(), url)
				if err != nil {
					fmt.Fprintf(w, "Error uploading %s to %s: %v\n", file.Name(), url, err)
				} else {
					fmt.Fprintf(w, "File %s uploaded successfully to %s\n", file.Name(), url)
				}
			}
		}
	}
}

// List the contents of the server files directory and return URLs as a list of strings
func listServerFilesContents(directory string) ([]string, error) {
	var urls []string

	files, err := ioutil.ReadDir(directory)
	if err != nil {
		return nil, err
	}

	for _, file := range files {
		if file.IsDir() {
			continue
		}

		filePath := filepath.Join(directory, file.Name())
		content, err := ioutil.ReadFile(filePath)
		if err != nil {
			return nil, err
		}

		// Append file contents to URLs
		urls = append(urls, strings.Split(string(content), "\n")...)
	}

	return urls, nil
}

// sendFile sends a file to a given URL using a POST request
func sendFile(file *os.File, fileName, url string) error {
	// Create a buffer and a multipart writer
	body := &bytes.Buffer{}
	writer := multipart.NewWriter(body)

	// Create a form file field
	part, err := writer.CreateFormFile("uploaded_file", filepath.Base(fileName))
	if err != nil {
		return err
	}

	// Copy the file content to the multipart writer
	_, err = io.Copy(part, file)
	if err != nil {
		return err
	}

	// Close the multipart writer
	writer.Close()

	// Make the HTTP POST request
	req, err := http.NewRequest("POST", url, body)
	if err != nil {
		return err
	}
	req.Header.Set("Content-Type", writer.FormDataContentType())

	client := &http.Client{}
	resp, err := client.Do(req)
	if err != nil {
		return err
	}
	defer resp.Body.Close()

	// Read the response
	respBody, err := ioutil.ReadAll(resp.Body)
	if err != nil {
		return err
	}

	// Check if the upload was successful
	if resp.StatusCode != http.StatusOK {
		return fmt.Errorf("failed to upload file: %s, response: %s", fileName, respBody)
	}

	return nil
}

const formTemplate = `
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>File Upload Form</title>
<style>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 20px;
    background-color: #f3f3f3;
}

.container {
    max-width: 600px;
    margin: 0 auto;
    background-color: #fff;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

h1 {
    text-align: center;
    margin-bottom: 20px;
}

form {
    margin-bottom: 20px;
}

label {
    font-weight: bold;
}

textarea {
    width: 100%;
    height: 100px;
    margin-bottom: 10px;
}

input[type="submit"] {
    background-color: #007bff;
    color: #fff;
    border: none;
    padding: 10px 20px;
    cursor: pointer;
}

input[type="submit"]:hover {
    background-color: #0056b3;
}
</style>
</head>
<body>

<div class="container">
    <h1>Upload File</h1>
    <form action="/upload" method="post" enctype="multipart/form-data">
        <label for="urls">Send files</label><br>
        <input type="submit" value="Upload File">
    </form>
</div>

</body>
</html>
`
