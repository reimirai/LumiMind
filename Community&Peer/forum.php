<?php
// Include the database connection
include 'db.php';

try {
    // Fetch all posts
    $stmt = $conn->prepare("
    SELECT p.id, p.title, p.content, p.created_at, u.Name, u.profile_image
    FROM posts p
    JOIN users u ON p.user_id = u.ID
    ORDER BY p.created_at DESC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $posts = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    die("Error fetching posts: " . $e->getMessage());
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) {
        return $diff . " seconds ago";
    } elseif ($diff < 3600) {
        return floor($diff / 60) . " minutes ago";
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . " hours ago";
    } else {
        return floor($diff / 86400) . " days ago";
    }
}
?>

<link rel="stylesheet" href="../css/forum.css" />
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700;900&display=swap" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>

<section class="forumcontent">
    <header class="content-header">
        <nav class="tabs">
            <button class="tab-button tab-active">
                <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/cc9ade3af8a282c4d9bef2274070365d937c7369?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                    class="tab-icon" alt="New posts icon" />
                <span class="tab-text">New</span>
            </button>
            <button class="tab-button">
                <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/4d690259d3bbffb61b6a9430035e1706f5b3c61e?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                    class="tab-icon" alt="Top posts icon" />
                <span class="tab-text">Top</span>
            </button>
            <button class="tab-button">
                <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/31d2ab442db79abe0144c0772b2e081f21f7eb85?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                    class="tab-icon" alt="Hot posts icon" />
                <span class="tab-text">Hot</span>
            </button>
            <button class="tab-button">
                <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/b38c740fee683885df778e8875ddb72344640cc4?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                    class="tab-icon" alt="Closed posts icon" />
                <span class="tab-text">Closed</span>
            </button>
        </nav>
        <button class="create-post-button" onclick="window.location.href='community.php?page=createpost'">
            <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/3a4ae20c7899fd28dee46e7b3753efbb2d14e17b?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                class="create-post-icon" alt="Plus icon" />
            <span class="create-post-text">Create a post</span>
        </button>
    </header>

    <section class="posts">
        <?php foreach ($posts as $post): ?>
            <article class="post-teaser">
                <header class="post-header">
                    <div class="user-info">
                         <?php 
                         if($post['profile_image'] !=null){
                         echo '<img src="data:image/jpeg;base64,' . $post['profile_image'] . '"  class="user-avatar" alt="User avatar" />';
                         }
                         else{
                          echo    '<img src="https://cdn.builder.io/api/v1/image/assets/TEMP/cde03525093ccfc2c4647574ce03f227442b0cfc?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"/>';
                         }
                         ?>
                       
                        <div class="user-details">
                            <h3 class="username"><?php echo htmlspecialchars($post['Name']); ?></h3>
                            <time class="post-time"><?php echo timeAgo($post['created_at']); ?></time>
                        </div>
                    </div>
                    <div class="relative">
                        <button class="post-menu-button" onclick="toggleDropdown(this)">
                            <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/f6252cccc4865cdb7a02f91ad0b2062da1f6fede?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                                class="post-menu-icon" alt="Menu icon" />
                        </button>
                        <!-- Dropdown menu -->
                        <div
                            class="hidden absolute right-0 mt-2 w-32 bg-white border border-gray-200 rounded shadow-lg z-10">
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
                <div class="post-content">
                    <h2 class="post-title">
                        <a href="community.php?page=post&id=<?php echo $post['id']; ?>"
                            class="text-blue-600 hover:underline">
                            <?php echo htmlspecialchars($post['title']); ?>
                        </a>
                    </h2>
                    <p class="post-description">
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
                                            <svg class="w-4 h-4 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                fill="none" viewBox="0 0 6 10">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.5" d="M5 1 1 5l4 4" />
                                            </svg>
                                            <span class="sr-only">Previous</span>
                                        </span>
                                    </button>
                                    <button type="button"
                                        class="absolute top-0 right-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none"
                                        data-carousel-next>
                                        <span
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-black/60 group-hover:bg-black/80">
                                            <svg class="w-4 h-4 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                fill="none" viewBox="0 0 6 10">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.5" d="m1 9 4-4-4-4" />
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
                </div>
                <footer class="post-footer">
                    <div class="post-tags">
                        <span class="tag">golang</span>
                        <span class="tag">linux</span>
                        <span class="tag">overflow</span>
                    </div>
                    <div class="post-activity">
                        <div class="activity-item">
                            <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/e59d77ee7fbaed69ceeeff92ab4c5b7fad5aa846?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                                class="activity-icon" alt="Likes icon" />
                            <span class="activity-count">125</span>
                        </div>
                        <div class="activity-item">
                            <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/a407287487a6689f749b4d0ac68c2475a6e8471e?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                                class="activity-icon" alt="Comments icon" />
                            <span class="activity-count">155</span>
                        </div>
                        <div class="activity-item">
                            <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/281c964aea204d1c44add82ee3a31d7d28244c9c?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                                class="activity-icon" alt="Shares icon" />
                            <span class="activity-count">15</span>
                        </div>
                    </div>
                </footer>
            </article>
        <?php endforeach; ?>

        <!-- <article class="post-teaser">
            <header class="post-header">
                <div class="user-info">
                    <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/cde03525093ccfc2c4647574ce03f227442b0cfc?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                        class="user-avatar" alt="User avatar" />
                    <div class="user-details">
                        <h3 class="username">Linuxoid</h3>
                        <time class="post-time">25 min ago</time>
                    </div>
                </div>
                <button class="post-menu-button">
                    <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/f6252cccc4865cdb7a02f91ad0b2062da1f6fede?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                        class="post-menu-icon" alt="Menu icon" />
                </button>
            </header>
            <div class="post-content">
                <h2 class="post-title">
                    What is a difference between Java nad JavaScript?
                </h2>
                <p class="post-description">
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                    Bibendum vitae etiam lectus amet enim.
                </p>
            </div>
            <footer class="post-footer">
                <div class="post-tags">
                    <span class="tag">java</span>
                    <span class="tag">javascript</span>
                    <span class="tag">wtf</span>
                </div>
                <div class="post-activity">
                    <div class="activity-item">
                        <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/e59d77ee7fbaed69ceeeff92ab4c5b7fad5aa846?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                            class="activity-icon" alt="Likes icon" />
                        <span class="activity-count">125</span>
                    </div>
                    <div class="activity-item">
                        <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/a407287487a6689f749b4d0ac68c2475a6e8471e?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                            class="activity-icon" alt="Comments icon" />
                        <span class="activity-count">155</span>
                    </div>
                    <div class="activity-item">
                        <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/281c964aea204d1c44add82ee3a31d7d28244c9c?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                            class="activity-icon" alt="Shares icon" />
                        <span class="activity-count">15</span>
                    </div>
                </div>
            </footer>
        </article>

        <article class="post-teaser">
            <header class="post-header">
                <div class="user-info">
                    <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/fa64cdc78bf87eb511b7f655489d2ae2c25c6683?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                        class="user-avatar" alt="User avatar" />
                    <div class="user-details">
                        <h3 class="username">AizhanMaratovna</h3>
                        <time class="post-time">2 days ago</time>
                    </div>
                </div>
                <button class="post-menu-button">
                    <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/f6252cccc4865cdb7a02f91ad0b2062da1f6fede?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                        class="post-menu-icon" alt="Menu icon" />
                </button>
            </header>
            <div class="post-content">
                <h2 class="post-title">
                    I want to study Svelte JS Framework. What is the best resourse
                    should I use?
                </h2>
                <p class="post-description">
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                    Consequat aliquet maecenas ut sit nulla
                </p>
            </div>
            <footer class="post-footer">
                <div class="post-tags">
                    <span class="tag">svelte</span>
                    <span class="tag">javascript</span>
                    <span class="tag">recomendations</span>
                </div>
                <div class="post-activity">
                    <div class="activity-item">
                        <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/e59d77ee7fbaed69ceeeff92ab4c5b7fad5aa846?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                            class="activity-icon" alt="Likes icon" />
                        <span class="activity-count">125</span>
                    </div>
                    <div class="activity-item">
                        <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/a407287487a6689f749b4d0ac68c2475a6e8471e?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                            class="activity-icon" alt="Comments icon" />
                        <span class="activity-count">155</span>
                    </div>
                    <div class="activity-item">
                        <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/281c964aea204d1c44add82ee3a31d7d28244c9c?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                            class="activity-icon" alt="Shares icon" />
                        <span class="activity-count">15</span>
                    </div>
                </div>
            </footer>
        </article>

        <article class="post-teaser">
            <header class="post-header">
                <div class="user-info">
                    <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/59d4dae2e00e0fa7aef4a0622cecdbcc3a5ec8bf?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                        class="user-avatar" alt="User avatar" />
                    <div class="user-details">
                        <h3 class="username">Lola</h3>
                        <time class="post-time">2 days ago</time>
                    </div>
                </div>
                <button class="post-menu-button">
                    <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/f6252cccc4865cdb7a02f91ad0b2062da1f6fede?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                        class="post-menu-icon" alt="Menu icon" />
                </button>
            </header>
            <div class="post-content">
                <h2 class="post-title">
                    I want to study Svelte JS Framework. What is the best resourse
                    should I use?
                </h2>
                <p class="post-description">
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                    Consequat aliquet maecenas ut sit nulla
                </p>
            </div>
            <footer class="post-footer">
                <div class="post-tags">
                    <span class="tag">golang</span>
                    <span class="tag">linux</span>
                    <span class="tag">overflow</span>
                </div>
                <div class="post-activity">
                    <div class="activity-item">
                        <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/e59d77ee7fbaed69ceeeff92ab4c5b7fad5aa846?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                            class="activity-icon" alt="Likes icon" />
                        <span class="activity-count">125</span>
                    </div>
                    <div class="activity-item">
                        <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/a407287487a6689f749b4d0ac68c2475a6e8471e?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                            class="activity-icon" alt="Comments icon" />
                        <span class="activity-count">155</span>
                    </div>
                    <div class="activity-item">
                        <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/281c964aea204d1c44add82ee3a31d7d28244c9c?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                            class="activity-icon" alt="Shares icon" />
                        <span class="activity-count">15</span>
                    </div>
                </div>
            </footer>
        </article> -->
    </section>
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
        const carousels = document.querySelectorAll('[data-carousel="static"]');

        carousels.forEach(carousel => {
            const items = carousel.querySelectorAll('[data-carousel-item]');
            const indicators = carousel.querySelectorAll('[data-carousel-slide-to]');
            const prevButton = carousel.querySelector('[data-carousel-prev]');
            const nextButton = carousel.querySelector('[data-carousel-next]');
            let currentIndex = 0;

            function showSlide(index) {
                items.forEach((item, i) => {
                    if (i === index) {
                        item.classList.remove('opacity-0', 'invisible');
                        item.classList.add('opacity-100', 'visible');
                    } else {
                        item.classList.add('opacity-0', 'invisible');
                        item.classList.remove('opacity-100', 'visible');
                    }
                });

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
    });
</script>