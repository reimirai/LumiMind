<link rel="stylesheet" href="styles.css" />
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700;900&display=swap" rel="stylesheet" />

<?php
// Include the database connection
include 'db.php';

try {
    // Fetch all posts
    $stmt = $conn->prepare("SELECT id, title, content, created_at FROM posts ORDER BY created_at DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    $posts = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    die("Error fetching posts: " . $e->getMessage());
}
?>

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
        <button class="create-post-button" onclick="window.location.href='index.php?page=createpost'">
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
                        <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/bc223bf7618cb0b0d7282822ebc179be812a602c?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                            class="user-avatar" alt="User avatar" />
                        <div class="user-details">
                            <h3 class="username">Golanginya</h3>
                            <time class="post-time">5 min ago</time>
                        </div>
                    </div>
                    <button class="post-menu-button">
                        <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/f6252cccc4865cdb7a02f91ad0b2062da1f6fede?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                            class="post-menu-icon" alt="Menu icon" />
                    </button>
                </header>
                <div class="post-content">
                    <h2 class="post-title">
                        <a href="index.php?page=post&id=1" class="text-blue-600 hover:underline">
                            <?php echo htmlspecialchars($post['title']); ?>
                        </a>
                    </h2>
                    <p class="post-description">
                        <?php echo nl2br(htmlspecialchars($post['content'])); ?>
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
            </article>
        <?php endforeach; ?>

        <article class="post-teaser">
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
        </article>
    </section>
</section>