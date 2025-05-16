<?php
include 'db.php';

$user_id = $_SESSION['user_id'] ?? null;
$group_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch group info
$stmt = $conn->prepare("SELECT * FROM peer_support_groups WHERE id = ?");
$stmt->bind_param("i", $group_id);
$stmt->execute();
$group = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$group) {
    echo "<div class='text-center mt-10 text-red-600'>Group not found.</div>";
    exit;
}

// Check if user is a member
$is_member = false;
if ($user_id) {
    $stmt = $conn->prepare("SELECT 1 FROM peer_support_group_members WHERE group_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $group_id, $user_id);
    $stmt->execute();
    $is_member = $stmt->get_result()->num_rows > 0;
    $stmt->close();
}

// Handle join request/leave request
if ($user_id && isset($_POST['join_group'])) {
    $stmt = $conn->prepare("INSERT INTO peer_support_group_members (group_id, user_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $group_id, $user_id);
    $stmt->execute();
    $stmt->close();
    $is_member = true;
} elseif ($user_id && isset($_POST['leave_group'])) {
    $stmt = $conn->prepare("DELETE FROM peer_support_group_members WHERE group_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $group_id, $user_id);
    $stmt->execute();
    $stmt->close();
    $is_member = false;
}

// Fetch posts in this group
$stmt = $conn->prepare("SELECT p.id, p.title, p.content, u.Name, u.avatar, p.created_at
                        FROM posts p
                        JOIN users u ON p.user_id = u.ID
                        WHERE p.group_id = ?
                        ORDER BY p.created_at DESC");
$stmt->bind_param("i", $group_id);
$stmt->execute();
$posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT p.id, p.title, p.content, p.created_at, u.Name, u.avatar,
        (SELECT COUNT(*) FROM post_likes pl WHERE pl.post_id = p.id) AS likes,
        (SELECT COUNT(*) FROM post_likes pl WHERE pl.post_id = p.id AND pl.user_id = ?) AS user_liked,
        (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count
    FROM posts p
    JOIN users u ON p.user_id = u.ID
    ORDER BY p.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$posts = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

function timeAgo($datetime)
{
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

<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700;900&display=swap" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>

<section class="min-w-[240px] grow px-[10px] overflow-hidden">
    <div class="w-full bg-white rounded-lg shadow p-6">
        <div class="flex items-center gap-4">
            <img src="<?php echo htmlspecialchars($group['icon_url']); ?>" alt="Group icon"
                class="w-14 h-14 rounded-full object-cover" />
            <div>
                <h1 class="text-2xl font-bold text-blue-700"><?php echo htmlspecialchars($group['name']); ?></h1>
                <div class="text-gray-500 text-sm"><?php echo htmlspecialchars($group['description']); ?></div>
            </div>
            <?php if ($user_id && !$is_member): ?>
                <form method="post" class="ml-auto">
                    <button type="submit" name="join_group"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded transition text-center">
                        Join Group
                    </button>
                </form>
            <?php elseif ($is_member): ?>
                <form method="post" class="ml-auto">
                    <button type="submit" name="leave_group"
                        class="bg-red-500 hover:bg-red-600 text-white font-semibold px-4 py-2 rounded transition text-center">
                        Leave Group
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <h2 class="text-xl font-semibold my-4 text-blue-800">Group Posts</h2>
    <?php if (empty($posts)): ?>
        <div class="text-gray-400 text-center py-8">No posts in this group yet.</div>
    <?php else: ?>
        <section class="max-w-full w-full">
            <?php foreach ($posts as $post): ?>
                <article
                    class="rounded-[5px] shadow-[2px_1px_5px_0_rgba(0,0,0,0.15)] bg-white w-full p-[25px_30px] overflow-hidden mb-[23px]">
                    <header class="flex w-full items-stretch gap-[20px] font-normal flex-wrap justify-between mt-0">
                        <div class="flex items-stretch gap-[15px]">
                            <img src="<?php echo htmlspecialchars($post['avatar']); ?>"
                                class="aspect-square object-contain object-center w-[40px] rounded-full flex-shrink-0"
                                alt="User avatar" />
                            <div class="flex flex-col items-stretch my-auto">
                                <h3 class="text-black text-[13px] tracking-[0.65px] font-normal">
                                    <?php echo htmlspecialchars($post['Name']); ?>
                                </h3>
                                <time
                                    class="text-gray-500 text-[10px] tracking-[0.5px] self-start mt-[5px] font-normal"><?php echo timeAgo($post['created_at']); ?></time>
                            </div>
                        </div>
                        <div class="relative">
                            <button class="self-center" onclick="toggleDropdown(this)">
                                <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/f6252cccc4865cdb7a02f91ad0b2062da1f6fede?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                                    class="aspect-square object-contain object-center w-[18px] flex-shrink-0" alt="Menu icon" />
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
                    <div class="mt-[15px] w-full overflow-hidden text-[14px] text-black tracking-[0.7px]">
                        <h2 class="font-bold text-[14px]">
                            <a href="community.php?page=post&id=<?php echo $post['id']; ?>"
                                class="text-blue-600 hover:underline">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>
                        </h2>
                        <p class="font-light leading-[25px] mt-[10px]">
                            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                        </p>

                        <div id="indicators-carousel-<?php echo $post['id']; ?>" class="relative w-full" data-carousel="static">
                            <?php
                            try {
                                $stmt = $conn->prepare("SELECT image_path FROM post_images WHERE post_id = ? ORDER BY id ASC");
                                $stmt->bind_param("i", $post['id']);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $images = $result->fetch_all(MYSQLI_ASSOC);
                                $stmt->close();

                                if (count($images) > 0): ?>
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
                                    <?php if (count($images) > 1): ?>
                                        <!-- Slider indicators -->
                                        <div
                                            class="absolute z-30 flex -translate-x-1/2 space-x-3 rtl:space-x-reverse bottom-5 left-1/2 bg-black/60 rounded-full px-2 py-2">
                                            <?php for ($i = 0; $i < count($images); $i++): ?>
                                                <button type="button" class="w-2 h-2 rounded-full"
                                                    aria-current="<?php echo $i === 0 ? 'true' : 'false'; ?>"
                                                    aria-label="Slide <?php echo $i + 1; ?>"
                                                    data-carousel-slide-to="<?php echo $i; ?>"></button>
                                            <?php endfor; ?>
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
                                <?php endif;
                            } catch (Exception $e) {
                                echo "<div class='text-red-500'>Error fetching images.</div>";
                            }
                            ?>
                        </div>
                    </div>
                    <footer>
                        <div
                            class="flex justify-between items-center mt-[15px] text-[13px] overflow-hidden whitespace-nowrap flex-wrap">
                            <?php if (!empty($post['user_liked'])): ?>
                                <form action="unlikepost.php" method="POST" class="inline">
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                    <button type="submit"
                                        class="flex items-center justify-start rounded-[5px] bg-blue-500 text-white font-bold min-h-[30px] px-5 py-2 gap-3 overflow-hidden border-none cursor-pointer hover:bg-blue-600">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="white">
                                            <path
                                                d="M313.4 32.9c26 5.2 42.9 30.5 37.7 56.5l-2.3 11.4c-5.3 26.7-15.1 52.1-28.8 75.2l144 0c26.5 0 48 21.5 48 48c0 18.5-10.5 34.6-25.9 42.6C497 275.4 504 288.9 504 304c0 23.4-16.8 42.9-38.9 47.1c4.4 7.3 6.9 15.8 6.9 24.9c0 21.3-13.9 39.4-33.1 45.6c.7 3.3 1.1 6.8 1.1 10.4c0 26.5-21.5 48-48 48l-97.5 0c-19 0-37.5-5.6-53.3-16.1l-38.5-25.7C176 420.4 160 390.4 160 358.3l0-38.3 0-48 0-24.9c0-29.2 13.3-56.7 36-75l7.4-5.9c26.5-21.2 44.6-51 51.2-84.2l2.3-11.4c5.2-26 30.5-42.9 56.5-37.7zM32 192l64 0c17.7 0 32 14.3 32 32l0 224c0 17.7-14.3 32-32 32l-64 0c-17.7 0-32-14.3-32-32L0 224c0-17.7 14.3-32 32-32z" />
                                        </svg>
                                        Like
                                        <span
                                            class="ml-2 font-normal text-white bg-blue-600 rounded px-2 py-0.5 text-xs"><?php echo $post['likes'] ?? 0; ?></span>
                                    </button>
                                </form>
                            <?php else: ?>
                                <form action="likepost.php" method="POST" class="inline">
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                    <button type="submit"
                                        class="flex items-center justify-start rounded-[5px] bg-blue-500 text-white font-bold min-h-[30px] px-5 py-2 gap-3 overflow-hidden border-none cursor-pointer hover:bg-blue-600">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="white">
                                            <path
                                                d="M313.4 32.9c26 5.2 42.9 30.5 37.7 56.5l-2.3 11.4c-5.3 26.7-15.1 52.1-28.8 75.2l144 0c26.5 0 48 21.5 48 48c0 18.5-10.5 34.6-25.9 42.6C497 275.4 504 288.9 504 304c0 23.4-16.8 42.9-38.9 47.1c4.4 7.3 6.9 15.8 6.9 24.9c0 21.3-13.9 39.4-33.1 45.6c.7 3.3 1.1 6.8 1.1 10.4c0 26.5-21.5 48-48 48l-97.5 0c-19 0-37.5-5.6-53.3-16.1l-38.5-25.7C176 420.4 160 390.4 160 358.3l0-38.3 0-48 0-24.9c0-29.2 13.3-56.7 36-75l7.4-5.9c26.5-21.2 44.6-51 51.2-84.2l2.3-11.4c5.2-26 30.5-42.9 56.5-37.7zM32 192l64 0c17.7 0 32 14.3 32 32l0 224c0 17.7-14.3 32-32 32l-64 0c-17.7 0-32-14.3-32-32L0 224c0-17.7 14.3-32 32-32z" />
                                        </svg>
                                        Like
                                        <span
                                            class="ml-2 font-normal text-white bg-blue-600 rounded px-2 py-0.5 text-xs"><?php echo $post['likes'] ?? 0; ?></span>
                                    </button>
                                </form>
                            <?php endif; ?>
                            <a href="community.php?page=post&id=<?php echo $post['id']; ?>"
                                class="flex items-center justify-start rounded-[5px] bg-orange-500 text-white font-bold min-h-[30px] px-5 py-2 gap-3 overflow-hidden border-none cursor-pointer hover:bg-orange-600">
                                <img src="https://cdn.builder.io/api/v1/image/assets/6403d12017614190bab75befab4eae62/0801cfcd2fdc867d984f25f5dde934722695646f?placeholderIfAbsent=true"
                                    class="" alt="Comment" />
                                Comment
                                <span class="ml-2 font-normal text-white bg-orange-600 rounded px-2 py-0.5 text-xs">
                                    <?php echo $post['comment_count'] ?? 0; ?>
                                </span>
                            </a>
                        </div>
                    </footer>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
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