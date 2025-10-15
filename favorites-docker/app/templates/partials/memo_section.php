<?php
// File: src/includes/memo_section.php
?>
<div class="bg-white p-6 rounded-xl shadow-md mb-8">
    <h2 class="text-2xl font-semibold mb-4 text-gray-700">메모</h2>
    <div id="memo-list" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <?php foreach ($memos as $memo): ?>
        <div class="bg-gray-50 p-4 rounded-lg border flex flex-col">
            <h3 class="font-bold text-lg text-gray-800 break-all"><?php echo htmlspecialchars($memo['title']); ?></h3>
            <p class="text-xs text-gray-400 my-2"><?php echo $memo['created_at']; ?></p>
            <p class="text-gray-700 whitespace-pre-wrap break-words"><?php echo htmlspecialchars($memo['content']); ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</div>