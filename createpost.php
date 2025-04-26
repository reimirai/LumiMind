<link rel="stylesheet" href="createpost.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet" />
<!-- <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script> -->



<section class="post-creation-section">
    <form action="submitpost.php" method="POST" enctype="multipart/form-data" class="post-form">
        <input type="text" name="title" required class="post-title-input" placeholder="Type your post title" />

        <textarea name="content" required class="post-content-input" placeholder="Type your post contents"></textarea>

        <!-- File Names Display -->
        <div class="file-preview hidden" id="filePreview">
            <ul id="fileList" class="file-list"></ul>
        </div>

        <div class="post-actions">
            <label for="images" class="add-image-button">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"
                    class="image-icon">
                    <path
                        d="M10.7917 2.125H3.20833C2.61002 2.125 2.125 2.61002 2.125 3.20833V10.7917C2.125 11.39 2.61002 11.875 3.20833 11.875H10.7917C11.39 11.875 11.875 11.39 11.875 10.7917V3.20833C11.875 2.61002 11.39 2.125 10.7917 2.125Z"
                        stroke="white" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path
                        d="M5.10417 5.91663C5.5529 5.91663 5.91667 5.55286 5.91667 5.10413C5.91667 4.65539 5.5529 4.29163 5.10417 4.29163C4.65544 4.29163 4.29167 4.65539 4.29167 5.10413C4.29167 5.55286 4.65544 5.91663 5.10417 5.91663Z"
                        stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    </path>
                    <path d="M11.875 8.62496L9.16666 5.91663L3.20833 11.875" stroke="white" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span class="button-text">Add Image</span>
            </label>
            <input type="file" id="images" name="images[]" accept="image/*" class="hidden" multiple />



            <button type="submit" class="post-button">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"
                    class="send-icon">
                    <path d="M12.4167 1.58337L6.45834 7.54171" stroke="white" stroke-linecap="round"
                        stroke-linejoin="round"></path>
                    <path d="M12.4167 1.58337L8.62501 12.4167L6.45834 7.54171L1.58334 5.37504L12.4167 1.58337Z"
                        stroke="white" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span class="button-text">Post</span>
            </button>
        </div>
    </form>
</section>

<div id="successModal" class="hidden fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-1/3">
        <h2 class="text-lg font-bold mb-4">Success</h2>
        <p class="modal-message text-gray-700"></p>
        <button class="close-modal mt-4 bg-blue-500 text-white px-4 py-2 rounded">Close</button>
    </div>
</div>

<script>
    // function dropdown() {
    //     return {
    //         open: false,
    //         tags: ['#javascript', '#bitcoin', '#design', '#blogging', '#tutorial'],
    //         selected: [],
    //         toggle() {
    //             this.open = !this.open;
    //         },
    //         updateSelection(event) {
    //             const value = event.target.value;
    //             if (event.target.checked) {
    //                 if (!this.selected.includes(value)) this.selected.push(value);
    //             } else {
    //                 this.selected = this.selected.filter(tag => tag !== value);
    //             }
    //         }
    //     }
    // }

    // Create a persistent DataTransfer object to manage files across multiple changes
    const dataTransfer = new DataTransfer();

    document.getElementById('images').addEventListener('change', function (event) {
        const fileInput = event.target;
        const files = fileInput.files;
        const filePreview = document.getElementById('filePreview');
        const fileList = document.getElementById('fileList');

        // Add new files from the input to the DataTransfer object
        Array.from(files).forEach(file => {
            dataTransfer.items.add(file);
        });

        // Clear the file list
        fileList.innerHTML = '';

        // Display all files in the DataTransfer object
        Array.from(dataTransfer.files).forEach((file, index) => {
            const listItem = document.createElement('li');
            listItem.classList.add('file-item');

            // Display the file name
            const fileName = document.createElement('span');
            fileName.textContent = file.name;

            // Add "X" button to remove the file
            const removeButton = document.createElement('button');
            removeButton.textContent = 'X';
            removeButton.classList.add('remove-file-button');
            removeButton.setAttribute('data-index', index);

            // Remove file functionality
            removeButton.addEventListener('click', function () {
                // Remove the file from the DataTransfer object
                dataTransfer.items.remove(index);

                // Update the file input with the remaining files
                fileInput.files = dataTransfer.files;

                // Re-render the file list
                renderFileList();
            });

            // Append file name and remove button to the list item
            listItem.appendChild(fileName);
            listItem.appendChild(removeButton);
            fileList.appendChild(listItem);
        });

        // Show or hide the file preview section
        if (dataTransfer.files.length > 0) {
            filePreview.classList.remove('hidden');
        } else {
            filePreview.classList.add('hidden');
        }
    });

    // Function to re-render the file list
    function renderFileList() {
        const filePreview = document.getElementById('filePreview');
        const fileList = document.getElementById('fileList');

        // Clear the file list
        fileList.innerHTML = '';

        // Display all files in the DataTransfer object
        Array.from(dataTransfer.files).forEach((file, index) => {
            const listItem = document.createElement('li');
            listItem.classList.add('file-item');

            // Display the file name
            const fileName = document.createElement('span');
            fileName.textContent = file.name;

            // Add "X" button to remove the file
            const removeButton = document.createElement('button');
            removeButton.textContent = 'X';
            removeButton.classList.add('remove-file-button');
            removeButton.setAttribute('data-index', index);

            // Remove file functionality
            removeButton.addEventListener('click', function () {
                // Remove the file from the DataTransfer object
                dataTransfer.items.remove(index);

                // Update the file input with the remaining files
                document.getElementById('images').files = dataTransfer.files;

                // Re-render the file list
                renderFileList();
            });

            // Append file name and remove button to the list item
            listItem.appendChild(fileName);
            listItem.appendChild(removeButton);
            fileList.appendChild(listItem);
        });

        // Show or hide the file preview section
        if (dataTransfer.files.length > 0) {
            filePreview.classList.remove('hidden');
        } else {
            filePreview.classList.add('hidden');
        }
    }

    document.querySelector('.post-form').addEventListener('submit', function (e) {
        e.preventDefault(); // Prevent the default form submission

        const formData = new FormData();

        // Add all files from the DataTransfer object to the FormData
        Array.from(dataTransfer.files).forEach(file => {
            formData.append('images[]', file);
        });

        // Add other form fields
        const title = document.querySelector('input[name="title"]').value;
        const content = document.querySelector('textarea[name="content"]').value;
        formData.append('title', title);
        formData.append('content', content);

        // Log all files being sent
        for (const [key, value] of formData.entries()) {
            console.log(key, value);
        }

        fetch('submitpost.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                console.log('Success:', data);
                if (data.success) {
                    // Show success modal or message
                    document.getElementById('successModal').classList.remove('hidden');
                    document.querySelector('.modal-message').textContent = data.message;
                } else {
                    console.error('Error:', data.message);
                }
            })
            .catch(error => console.error('Error:', error));
    });
    // Close the modal
    document.querySelector('#successModal .close-modal').addEventListener('click', function () {
        document.querySelector('#successModal').classList.add('hidden');
    });
</script>