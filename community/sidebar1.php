<?php
include 'db.php';

$user_id = $_SESSION['user_id'] ?? null;
$joined_groups = [];
if ($user_id) {
    $stmt = $conn->prepare("SELECT g.id, g.name, g.icon_url, 
        (SELECT COUNT(*) FROM peer_support_group_members m2 WHERE m2.group_id = g.id) AS member_count
        FROM peer_support_groups g
        JOIN peer_support_group_members m ON g.id = m.group_id
        WHERE m.user_id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $joined_groups = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$current_page = $_GET['page'] ?? 'forum';
$from = $_GET['from'] ?? null;

// If viewing a post and came from yourposts, highlight "Your Posts"
if ($from === 'yourposts') {
    $current_page = 'yourposts';
} else if ($from === 'forum') {
    $current_page = 'forum';
} else if ($from === 'group') {
    $current_page = 'group';
}
?>

<link rel="stylesheet" href="sidebar1.css" />

<nav class="sidebar1">
    <h1 class="sidebar-title">Community & Peer Support</h1>

    <section class="menu-section">
        <h2 class="section-header" style="padding-left: 50px;">Menu</h2>

        <ul class="menu-list">
            <li class="menu-item <?php echo $current_page === 'forum' ? 'menu-item-selected' : ''; ?>">
                <a href="community.php?page=forum" <?php echo $current_page === 'forum' ? 'class="flex" style="gap: 40px 45px;"' : ''; ?>>
                    <?php if ($current_page === 'forum'): ?>
                        <span class="selection-indicator"></span>
                    <?php endif; ?>
                    <div class="menu-item-content">
                        <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/c4b029bc79cbf85a43a6726ffb4e99572bf5aa06?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                            alt="Posts icon" class="menu-icon" />
                        <span class="menu-text">Posts</span>
                    </div>
                </a>
            </li>

            <li
                class="menu-item <?php echo $current_page === 'grouplist' || $current_page === 'group' ? 'menu-item-selected' : ''; ?>">
                <a href="community.php?page=grouplist" <?php echo $current_page === 'grouplist' || $current_page === 'group' ? 'class="flex" style="gap: 40px 45px;"' : ''; ?>>
                    <?php if ($current_page === 'grouplist' || $current_page === 'group'): ?>
                        <span class="selection-indicator"></span>
                    <?php endif; ?>
                    <div class="menu-item-content">
                        <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/1ff8c5fd1868e34f48fdfa77d11ef2ea8419551c?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                            alt="Tags icon" class="menu-icon" />
                        <span class="menu-text">Peer Support Groups</span>
                    </div>
                </a>
            </li>

            <li class="menu-item <?php echo $current_page === 'yourposts' ? 'menu-item-selected' : ''; ?>">
                <a href="community.php?page=yourposts" <?php echo $current_page === 'yourposts' ? 'class="flex" style="gap: 40px 45px;"' : ''; ?>>
                    <?php if ($current_page === 'yourposts'): ?>
                        <span class="selection-indicator"></span>
                    <?php endif; ?>
                    <div class="menu-item-content">
                        <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/eb3dc4928ea19a2ca00d79f802a981b8f202be2a?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                            alt="Your Posts icon" class="menu-icon" />
                        <span class="menu-text">Your Posts</span>
                    </div>
                </a>
            </li>
        </ul>
    </section>

    <section class="support-groups-section">
        <h2 class="section-header">JOINED GROUPS</h2>
        <ul class="tag-list">
            <?php if (!empty($joined_groups)): ?>
                <?php foreach ($joined_groups as $group): ?>
                    <a href="community.php?page=group&id=<?php echo $group['id']; ?>" class="tag-item">
                        <img src="<?php echo htmlspecialchars($group['icon_url']); ?>" alt="JavaScript tag" class="tag-icon" />
                        <div class="tag-details">
                            <span class="tag-name hover:text-blue-600 hover:underline"><?php echo htmlspecialchars($group['name']); ?></span>
                            <span class="tag-stats"><?php echo number_format($group['member_count']); ?> Members</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="tag-item text-gray-400 px-2">You haven't joined any groups yet.</li>
            <?php endif; ?>
        </ul>
    </section>
</nav>