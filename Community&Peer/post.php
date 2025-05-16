<?php
// Include database connection
include 'db.php';

// Get the post ID from the URL
$postId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($postId > 0) {
    // Fetch the post from the database
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $post = $result->fetch_assoc();
    } else {
        echo "<p>Post not found.</p>";
        exit;
    }
} else {
    echo "<p>Invalid post ID.</p>";
    exit;
}

// Fetch all comments and replies for the post
$stmt = $conn->prepare("
    SELECT c.id, c.content, c.parent_id, c.created_at, u.Name ,u.profileimage
    FROM comments c
    JOIN users u ON c.user_id = u.ID
    WHERE c.post_id = ?
    ORDER BY c.parent_id ASC, c.created_at ASC
");
$stmt->bind_param("i", $postId);
$stmt->execute();
$result = $stmt->get_result();
$comments = $result->fetch_all(MYSQLI_ASSOC);

$nestedComments = [];
foreach ($comments as $comment) {
    if ($comment['parent_id'] === null) {
        // Top-level comment
        $nestedComments[$comment['id']] = $comment;
        $nestedComments[$comment['id']]['replies'] = [];
    } else {
        // Reply to a comment
        $nestedComments[$comment['parent_id']]['replies'][] = $comment;
    }
}
?>

<link rel="stylesheet" href="../css/post.css" />
<link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>

<section class="post-comments">
    <article class="post-full">
        <header class="post-header">
            <div class="user-info">
                <img src="https://cdn.builder.io/api/v1/image/assets/6403d12017614190bab75befab4eae62/0dcec91c8816afdc7ba65e51c71401437b24100c?placeholderIfAbsent=true"
                    class="user-avatar" alt="User avatar" />
                <div class="user-details">
                    <p class="username">@Golanginya</p>
                    <time class="post-time">12 November 2020 19:35</time>
                </div>
            </div>
            <div class="relative">
                <button class="post-menu-button" onclick="toggleDropdown(this)">
                    <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/f6252cccc4865cdb7a02f91ad0b2062da1f6fede?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                        class="post-menu-icon" alt="Menu icon" />
                </button>
                <!-- Dropdown menu -->
                <div class="hidden absolute right-0 mt-2 w-32 bg-white border border-gray-200 rounded shadow-lg z-10">
                    <a href="community.php?page=editpost&id=<?php echo $post['id']; ?>&ref=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Edit</a>
                    <form action="deletepost.php" method="POST" class="block">
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <button type="submit"
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Delete</button>
                    </form>
                </div>
            </div>
        </header>
        <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
        <p class="post-content">
            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
        </p>
        <div id="indicators-carousel" class="relative w-full" data-carousel="static">
            <?php
            try {
                $stmt = $conn->prepare("SELECT image_path FROM post_images WHERE post_id = ? ORDER BY id ASC");
                $stmt->bind_param("i", $post['id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $images = $result->fetch_all(MYSQLI_ASSOC); // Fetch all images as an associative array
                $stmt->close();

                if (count($images) > 0): // Check if there are any images
                    ?>
                    <!-- Carousel wrapper -->
                    <div class="relative h-56 overflow-hidden rounded-lg md:h-96">
                        <?php
                        $isFirstImage = true;
                        foreach ($images as $image) {
                            $imagePath = htmlspecialchars($image['image_path']);
                            echo '<div class="opacity-0 invisible duration-700 ease-in-out transition-all" ' . ($isFirstImage ? 'data-carousel-item="active">' : 'data-carousel-item>');
                            echo '<img src="' . $imagePath . '" alt="Post Image" class="w-full h-full object-scale-down">';
                            echo '</div>';
                            $isFirstImage = false;
                        }
                        ?>
                    </div>
                    <?php if (count($images) > 1): // Show indicators and controls only if there is more than one image ?>
                        <!-- Slider indicators -->
                        <div
                            class="absolute z-30 flex -translate-x-1/2 space-x-3 rtl:space-x-reverse bottom-5 left-1/2 bg-black/60 rounded-full px-2 py-2">
                            <?php
                            for ($i = 0; $i < count($images); $i++) {
                                echo '<button type="button" class="w-2 h-2 rounded-full" aria-current="' . ($i === 0 ? 'true' : 'false') . '" aria-label="Slide ' . ($i + 1) . '" data-carousel-slide-to="' . $i . '"></button>';
                            }
                            ?>
                        </div>
                        <!-- Slider controls -->
                        <button type="button"
                            class="absolute top-0 left-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none"
                            data-carousel-prev>
                            <span
                                class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-black/60 group-hover:bg-black/80">
                                <svg class="w-4 h-4 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 6 10">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M5 1 1 5l4 4" />
                                </svg>
                                <span class="sr-only">Previous</span>
                            </span>
                        </button>
                        <button type="button"
                            class="absolute top-0 right-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none"
                            data-carousel-next>
                            <span
                                class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-black/60 group-hover:bg-black/80">
                                <svg class="w-4 h-4 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 6 10">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="m1 9 4-4-4-4" />
                                </svg>
                                <span class="sr-only">Next</span>
                            </span>
                        </button>
                    <?php endif; ?>
                    <?php
                endif; // End of image check
            } catch (Exception $e) {
                echo "Error fetching images: " . $e->getMessage();
            }
            ?>
        </div>
        <div class="action-buttons">
            <button class="like-button">
                <img src="https://cdn.builder.io/api/v1/image/assets/6403d12017614190bab75befab4eae62/c20be7f95abbbbcab1eb11265dde49832d906682?placeholderIfAbsent=true"
                    class="button-icon" alt="Like" />
                <span class="button-text">Like</span>
            </button>
            <button class="comment-button">
                <img src="https://cdn.builder.io/api/v1/image/assets/6403d12017614190bab75befab4eae62/9a9a655dec4f9f81a13f324c44091f40b0e6a9f4?placeholderIfAbsent=true"
                    class="button-icon" alt="Comment" />
                <span class="button-text">Comment</span>
            </button>
        </div>
    </article>
    <form action="addcomment.php" method="POST">
        <div class="comment-input">
            <input type="hidden" name="post_id" value="<?php echo $postId; ?>">
            <input type="hidden" name="parent_id" value="NULL"> <!-- Top-level comment -->
            <div class="input-container">
                <textarea name="content" class="comment-textarea" placeholder="Type here your comment here"
                    required></textarea>
            </div>
            <div class="comment-actions">
                <!-- <button class="cancel-button">Cancel</button> -->
                <button type="submit" class="submit-comment-button">
                    <img src="https://cdn.builder.io/api/v1/image/assets/6403d12017614190bab75befab4eae62/0801cfcd2fdc867d984f25f5dde934722695646f?placeholderIfAbsent=true"
                        class="button-icon" alt="Comment" />
                    <span class="button-text">Comment</span>
                </button>
            </div>
        </div>
    </form>
    <h2 class="comments-heading">Comments</h2>
    <?php foreach ($nestedComments as $comment): ?>
        <article class="comment-thread">
            <div class="comment-content">
                <header class="comment-header">
                    <div class="commenter-info">
                        <img src="https://cdn.builder.io/api/v1/image/assets/6403d12017614190bab75befab4eae62/0f6a9b088819061ad05bca21bee18205818255b4?placeholderIfAbsent=true"
                            class="commenter-avatar" alt="User avatar" />
                        <div class="commenter-details">
                            <p class="commenter-name"><?php echo htmlspecialchars($comment['Name']); ?></p>
                            <time class="comment-time"><?php echo htmlspecialchars($comment['created_at']); ?></time>
                        </div>
                    </div>
                    <img src="https://cdn.builder.io/api/v1/image/assets/6403d12017614190bab75befab4eae62/d510a0dc290d5bb4be1ea52e219ca59f591b8f7d?placeholderIfAbsent=true"
                        class="menu-icon" alt="Menu" />
                </header>
                <p class="comment-text">
                    <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                </p>
                <div class="comment-footer">
                    <hr class="comment-divider" />
                    <div class="comment-actions-row">
                        <div class="vote-buttons">
                            <div class="dislike-count">
                                <img src="https://cdn.builder.io/api/v1/image/assets/6403d12017614190bab75befab4eae62/0378e41f74a62377ea875df83be9dd57cd8e42c8?placeholderIfAbsent=true"
                                    class="vote-icon" alt="Dislike" />
                                <span>12</span>
                            </div>
                            <div class="like-count">
                                <img src="https://cdn.builder.io/api/v1/image/assets/6403d12017614190bab75befab4eae62/94c83d5b958e8fccd1ae43b795978d6959bfe52c?placeholderIfAbsent=true"
                                    class="vote-icon" alt="Like" />
                                <span>3</span>
                            </div>
                        </div>
                        <div class="comment-controls">
                            <button class="replies-toggle">
                                <img src="https://cdn.builder.io/api/v1/image/assets/6403d12017614190bab75befab4eae62/a3b4cd2ec2f61c080156d173595627e6a219e13a?placeholderIfAbsent=true"
                                    class="control-icon" alt="Toggle replies" />
                                <span>Hide All Replies (2)</span>
                            </button>
                            <button class="reply-button" data-comment-id="<?php echo $comment['id']; ?>">
                                <img src="https://cdn.builder.io/api/v1/image/assets/6403d12017614190bab75befab4eae62/32159c5f98d82b931cc2ae5dbeab59ee3af0719a?placeholderIfAbsent=true"
                                    class="control-icon" alt="Reply" />
                                <span>Reply</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reply Form (Initially Hidden) -->
            <div class="reply-form hidden mt-4" id="reply-form-<?php echo $comment['id']; ?>">
                <form action="addcomment.php" method="POST" class="space-y-2">
                    <input type="hidden" name="post_id" value="<?php echo $postId; ?>">
                    <input type="hidden" name="parent_id" value="<?php echo $comment['id']; ?>">
                    <!-- Reply to this comment -->
                    <textarea name="content"
                        class="reply-textarea w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Type your reply here" required></textarea>
                    <button type="submit"
                        class="submit-reply-button bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">Submit
                        Reply</button>
                </form>
            </div>

            <?php if (!empty($comment['replies'])): ?>
                <h3 class="replies-heading">Replies</h3>
                <?php foreach ($comment['replies'] as $reply): ?>
                    <article class="nested-comment">
                        <div class="nested-comment-content">
                            <p class="nested-comment-text">
                                <?php echo nl2br(htmlspecialchars($reply['content'])); ?>
                            </p>
                            <div class="nested-comment-footer">
                                <hr class="comment-divider" />
                                <div class="nested-comment-info">
                                    <p class="comment-author">by @<?php echo htmlspecialchars($reply['Name']); ?></p>
                                    <button class="reply-button">
                                        <img src="https://cdn.builder.io/api/v1/image/assets/6403d12017614190bab75befab4eae62/32159c5f98d82b931cc2ae5dbeab59ee3af0719a?placeholderIfAbsent=true"
                                            class="control-icon" alt="Reply" />
                                        <span>Reply</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
    <!-- <article class="comment-thread">
        <div class="comment-content">
            <header class="comment-header">
                <div class="commenter-info">
                    <img src="https://cdn.builder.io/api/v1/image/assets/6403d12017614190bab75befab4eae62/30a8760bd47777dcdfd529f26e5873dde33e3b21?placeholderIfAbsent=true"
                        class="commenter-avatar" alt="User avatar" />
                    <div class="commenter-details">
                        <p class="commenter-name">@morgenshtern</p>
                        <time class="comment-time">12 November 2020 19:35</time>
                    </div>
                </div>
                <img src="https://cdn.builder.io/api/v1/image/assets/6403d12017614190bab75befab4eae62/d510a0dc290d5bb4be1ea52e219ca59f591b8f7d?placeholderIfAbsent=true"
                    class="menu-icon" alt="Menu" />
            </header>
            <p class="comment-text">
                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ornare rutrum
                amet, a nunc mi lacinia in iaculis. Pharetra ut integer nibh urna.
                Placerat ut adipiscing nulla lectus vulputate massa, scelerisque.
                Netus nisl nulla placerat dignissim ipsum arcu.
            </p>
            <div class="comment-footer">
                <hr class="comment-divider" />
                <div class="comment-actions-row">
                    <div class="vote-buttons">
                        <div class="dislike-count">
                            <img src="https://cdn.builder.io/api/v1/image/assets/6403d12017614190bab75befab4eae62/a7d2124bdca5ab85984433bab6721ec7278a1c16?placeholderIfAbsent=true"
                                class="vote-icon" alt="Dislike" />
                            <span>256</span>
                        </div>
                        <div class="like-count">
                            <img src="https://cdn.builder.io/api/v1/image/assets/6403d12017614190bab75befab4eae62/94c83d5b958e8fccd1ae43b795978d6959bfe52c?placeholderIfAbsent=true"
                                class="vote-icon" alt="Like" />
                            <span>43</span>
                        </div>
                    </div>
                    <div class="comment-controls">
                        <button class="replies-toggle">
                            <img src="https://cdn.builder.io/api/v1/image/assets/6403d12017614190bab75befab4eae62/6645bbf692c5ea4ef498f05cf95e6953387bf713?placeholderIfAbsent=true"
                                class="control-icon" alt="Toggle replies" />
                            <span>Show All Replies (21)</span>
                        </button>
                        <button class="reply-button">
                            <img src="https://cdn.builder.io/api/v1/image/assets/6403d12017614190bab75befab4eae62/9c9de4cc7088f9c258724f68944f72dd3320e555?placeholderIfAbsent=true"
                                class="control-icon" alt="Reply" />
                            <span>Reply</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </article>
    <article class="comment-thread">
        <div class="comment-content">
            <header class="comment-header">
                <div class="commenter-info">
                    <img src="https://cdn.builder.io/api/v1/image/assets/6403d12017614190bab75befab4eae62/c939e98da08bca88eb32e6b4cf81e79481f16087?placeholderIfAbsent=true"
                        class="commenter-avatar" alt="User avatar" />
                    <div class="commenter-details">
                        <p class="commenter-name">@kizaru</p>
                        <time class="comment-time">12 November 2020 19:35</time>
                    </div>
                </div>
                <img src="https://cdn.builder.io/api/v1/image/assets/6403d12017614190bab75befab4eae62/d510a0dc290d5bb4be1ea52e219ca59f591b8f7d?placeholderIfAbsent=true"
                    class="menu-icon" alt="Menu" />
            </header>
            <p class="comment-text">
                Mi ac id faucibus laoreet. Nulla quis in interdum imperdiet. Lacus
                mollis massa netus.
            </p>
            <div class="comment-footer">
                <hr class="comment-divider" />
                <div class="comment-actions-row">
                    <div class="vote-buttons">
                        <div class="dislike-count">
                            <img src="https://cdn.builder.io/api/v1/image/assets/6403d12017614190bab75befab4eae62/a7d2124bdca5ab85984433bab6721ec7278a1c16?placeholderIfAbsent=true"
                                class="vote-icon" alt="Dislike" />
                            <span>1</span>
                        </div>
                        <div class="like-count">
                            <img src="https://cdn.builder.io/api/v1/image/assets/6403d12017614190bab75befab4eae62/94c83d5b958e8fccd1ae43b795978d6959bfe52c?placeholderIfAbsent=true"
                                class="vote-icon" alt="Like" />
                            <span>0</span>
                        </div>
                    </div>
                    <div class="comment-controls">
                        <button class="reply-button">
                            <img src="https://cdn.builder.io/api/v1/image/assets/6403d12017614190bab75befab4eae62/9c9de4cc7088f9c258724f68944f72dd3320e555?placeholderIfAbsent=true"
                                class="control-icon" alt="Reply" />
                            <span>Reply</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </article> -->
</section>

<script>
    function toggleDropdown(button) {
        const dropdown = button.nextElementSibling;
        dropdown.classList.toggle('hidden');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function (event) {
        const dropdowns = document.querySelectorAll('.relative .hidden');
        dropdowns.forEach(dropdown => {
            if (!dropdown.parentElement.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
        const items = document.querySelectorAll('[data-carousel-item]');
        const indicators = document.querySelectorAll('[data-carousel-slide-to]');
        const prevButton = document.querySelector('[data-carousel-prev]');
        const nextButton = document.querySelector('[data-carousel-next]');
        let currentIndex = 0;

        function showSlide(index) {
            items.forEach((item, i) => {
                if (i === index) {
                    item.classList.remove('opacity-0', 'invisible');
                    item.classList.add('opacity-100', 'visible'); // Make active image fully visible
                } else {
                    item.classList.add('opacity-0', 'invisible');
                    item.classList.remove('opacity-100', 'visible'); // Hide inactive images
                }
            });

            // Update indicators
            indicators.forEach((btn, i) => {
                btn.setAttribute('aria-current', i === index ? 'true' : 'false');
            });

            if (prevButton) {
                prevButton.style.display = index === 0 ? 'none' : 'flex';
            }

            if (nextButton) {
                nextButton.style.display = index === items.length - 1 ? 'none' : 'flex';
            }

            currentIndex = index;
        }

        // Indicator click event
        indicators.forEach((btn, index) => {
            btn.addEventListener('click', () => showSlide(index));
        });

        if (prevButton) {
            prevButton.addEventListener('click', () => {
                const newIndex = (currentIndex - 1 + items.length) % items.length;
                showSlide(newIndex);
            });
        }

        if (nextButton) {
            nextButton.addEventListener('click', () => {
                const newIndex = (currentIndex + 1) % items.length;
                showSlide(newIndex);
            });
        }

        // Initialize the carousel
        showSlide(currentIndex);
    });

    document.addEventListener('DOMContentLoaded', function () {
        // Get all reply buttons
        const replyButtons = document.querySelectorAll('.reply-button');

        replyButtons.forEach(button => {
            button.addEventListener('click', function () {
                const commentId = this.getAttribute('data-comment-id'); // Get the comment ID
                const replyForm = document.getElementById(`reply-form-${commentId}`); // Get the corresponding reply form

                // Toggle the visibility of the reply form
                replyForm.classList.toggle('hidden');
            });
        });
    });
</script>