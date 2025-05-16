<link rel="stylesheet" href="../css/popup.css"/>

<div id="successPopup" class="popup" style="display:none;">
  <div class="popup-content">
    <p>Note added successfully!</p>
    <button class="confirm-btn" onclick="goBack()">OK</button>
  </div>
</div>

<div id="deletePopup" class="popup" style="display:none;">
  <div class="popup-content">
    <p>Note deleted successfully!</p>
    <button class="confirm-btn" onclick="goBack()">OK</button>
  </div>
</div>

<script>
function showPopup() {
    document.getElementById('successPopup').style.display = 'block';
}

function showDeletePopup() {
    document.getElementById('deletePopup').style.display = 'block';
}

function goBack() {
    window.location.href = 'StickyNote.php';
}
</script>

