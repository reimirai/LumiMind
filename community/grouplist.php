<?php
include 'db.php';

// Fetch all groups with member count
$sql = "SELECT g.id, g.name, g.description, g.icon_url, 
               COUNT(m.user_id) AS member_count
        FROM peer_support_groups g
        LEFT JOIN peer_support_group_members m ON g.id = m.group_id
        GROUP BY g.id";
$result = $conn->query($sql);
$groups = $result->fetch_all(MYSQLI_ASSOC);
?>

<section class="bg-gray-50 min-w-[240px] grow">
    <div class="w-full bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold mb-6 text-blue-700 flex items-center gap-3">
            <img src="https://cdn.builder.io/api/v1/image/assets/TEMP/1ff8c5fd1868e34f48fdfa77d11ef2ea8419551c?placeholderIfAbsent=true&apiKey=6403d12017614190bab75befab4eae62"
                alt="Tags icon" class="w-8 h-8" />
            Peer Support Groups
        </h1>
        <ul class="space-y-4">
            <?php foreach ($groups as $group): ?>
                <li class="flex items-center gap-4 p-4 bg-gray-100 rounded hover:bg-blue-50 transition">
                    <img src="<?php echo htmlspecialchars($group['icon_url']); ?>" alt="Group icon"
                        class="w-10 h-10 rounded-full object-cover" />
                    <div class="flex-1">
                        <div class="font-semibold text-blue-800"><?php echo htmlspecialchars($group['name']); ?></div>
                        <div class="text-xs text-gray-500">
                            <?php echo number_format($group['member_count']); ?> members
                        </div>
                        <div class="text-xs text-gray-500 pt-1">
                            <?php if (!empty($group['description'])): ?>
                                <?php echo htmlspecialchars($group['description']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <a href="community.php?page=group&id=<?php echo $group['id']; ?>"
                        class="text-blue-600 hover:underline text-sm font-medium">View</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>