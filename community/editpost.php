<?php
include 'db.php';

// Validate the `id` parameter
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid post ID.");
}

$postId = $_GET['id']; // Get the post ID from the query parameter

// Fetch the post details
try {
    $stmt = $conn->prepare("SELECT title, content FROM posts WHERE id = ?");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc(); // Fetch the post details
    $stmt->close();

    if (!$post) {
        die("Post not found."); // Handle the case where the post does not exist
    }
} catch (Exception $e) {
    die("Error fetching post: " . $e->getMessage());
}

// Fetch the images associated with the post
try {
    $stmt = $conn->prepare("SELECT id, image_path FROM post_images WHERE post_id = ?");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    $images = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    die("Error fetching images: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];

    try {
        // Update the post's title and content in the database
        $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
        $stmt->bind_param("ssi", $title, $content, $postId);
        if (!$stmt->execute()) {
            throw new Exception('Error updating the post.');
        }
        $stmt->close();

        // Handle image uploads
        if (isset($_FILES['images']) && count($_FILES['images']['tmp_name']) > 0) {
            $uploadDir = 'uploads/';

            foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $originalName = basename($_FILES['images']['name'][$key]);
                    $imageName = time() . '_' . $originalName; // Add a timestamp to avoid conflicts
                    $imagePath = $uploadDir . $imageName;

                    // Move the uploaded file to the desired directory
                    if (move_uploaded_file($tmpName, $imagePath)) {
                        // Save the new image path to the `post_images` table
                        $stmt = $conn->prepare("INSERT INTO post_images (post_id, image_path) VALUES (?, ?)");
                        $stmt->bind_param("is", $postId, $imagePath);
                        if (!$stmt->execute()) {
                            throw new Exception('Error saving the image path to the database.');
                        }
                        $stmt->close();
                    } else {
                        throw new Exception('Error moving the uploaded file.');
                    }
                }
            }
        }

        // Redirect back to the previous page
        $previousPage = $_POST['previousPage'] ?? 'community.php?page=forum';
        header("Location: $previousPage");
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<section class="flex flex-col items-center gap-5 w-[755px] grow">
    <div class="w-[720px] p-[30px_40px] shadow-[2px_1px_5px_rgba(0,0,0,0.15)] rounded-[5px] bg-white">
        <h1 class="text-2xl font-bold mb-4">Edit Post</h1>
        <?php if (isset($error)): ?>
            <p class="text-red-500"><?php echo $error; ?></p>
        <?php endif; ?>

        <!-- Main Form for Editing Post -->
        <form action="editpost.php?id=<?php echo $postId; ?>" method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="mb-4">
                <label for="content" class="block text-sm font-medium text-gray-700">Content</label>
                <textarea id="content" name="content" rows="5"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($post['content']); ?></textarea>
            </div>
            
            <!-- File preview section -->
            <div class="mb-4">
                <ul id="file-list" class="mt-2 space-y-2"></ul>
            </div>

            <div class="mb-4 flex justify-between">
                <label for="images"
                    class="flex items-center p-[12px_20px] rounded-[5px] cursor-pointer border-none outline-none bg-[#1682fd]">
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
                    <span class="text-[12px] text-white font-black ml-[12px]">Add Image</span>
                </label>
                <input type="file" id="images" name="images[]" accept="image/*" class="hidden" multiple />
                <input type="hidden" name="previousPage" value="<?php echo htmlspecialchars($_GET['ref'] ?? 'community.php?page=forum'); ?>">

                <button type="submit"
                    class="flex items-center p-[12px_20px] rounded-[5px] cursor-pointer border-none outline-none bg-[#f48023]">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"
                        class="send-icon">
                        <path d="M12.4167 1.58337L6.45834 7.54171" stroke="white" stroke-linecap="round"
                            stroke-linejoin="round"></path>
                        <path d="M12.4167 1.58337L8.62501 12.4167L6.45834 7.54171L1.58334 5.37504L12.4167 1.58337Z"
                            stroke="white" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    <span class="text-[12px] text-white font-black ml-[12px]">Save Changes</span>
                </button>
            </div>
        </form>

        <!-- Section for Saved Images -->
        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700">Saved Images</label>
            <ul class="mt-2 space-y-2">
                <?php foreach ($images as $image): ?>
                    <li class="flex items-center justify-between bg-gray-100 px-4 py-2 rounded shadow">
                        <a href="<?php echo htmlspecialchars($image['image_path']); ?>" target="_blank"
                            class="text-sm text-blue-500 hover:underline"><?php echo basename($image['image_path']); ?></a>
                        <!-- Separate Delete Form -->
                        <form action="deleteimage.php" method="POST" class="inline">
                            <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                            <button type="submit" class="text-red-500 hover:text-red-700 font-bold">x</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>

<script>
    const fileInput = document.getElementById('images');
    const fileList = document.getElementById('file-list');

    fileInput.addEventListener('change', function () {
        // Clear the file list
        fileList.innerHTML = '';

        // Loop through selected files
        Array.from(fileInput.files).forEach((file, index) => {
            const listItem = document.createElement('li');
            listItem.className = 'flex items-center justify-between bg-gray-100 px-4 py-2 rounded shadow';

            // Display the filename
            const fileName = document.createElement('span');
            fileName.textContent = file.name;
            fileName.className = 'text-sm text-gray-700';

            // Add a delete button
            const removeButton = document.createElement('button');
            removeButton.textContent = 'x';
            removeButton.className = 'text-red-500 hover:text-red-700 font-bold';
            removeButton.addEventListener('click', function () {
                // Remove the file from the input
                const dataTransfer = new DataTransfer();
                Array.from(fileInput.files).forEach((f, i) => {
                    if (i !== index) {
                        dataTransfer.items.add(f);
                    }
                });
                fileInput.files = dataTransfer.files;

                // Remove the list item
                listItem.remove();
            });

            listItem.appendChild(fileName);
            listItem.appendChild(removeButton);
            fileList.appendChild(listItem);
        });
    });
</script>