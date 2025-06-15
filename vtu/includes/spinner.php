<?php
// Fetch site logo from settings table for spinner
$siteLogo = 'assets/images/logo.png';
try {
    $db = DB::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT site_logo FROM settings LIMIT 1");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && !empty($row['site_logo'])) {
        $siteLogo = $row['site_logo'];
    }
} catch (Exception $e) {
    // fallback to default
}
?>
<div id="vtu-loader" style="position:fixed;top:0;left:0;width:100vw;height:100vh;background:#f9fafb;z-index:9999;display:flex;align-items:center;justify-content:center;">
    <div style="text-align:center;">
        <img src="<?php echo htmlspecialchars($siteLogo); ?>" alt="Site Logo" style="width:70px;height:70px;animation:vtu-spin 1.2s linear infinite;filter:drop-shadow(0 0 16px #2563eb);border-radius:50%;background:#fff;padding:6px;">
        <div style="margin-top:1rem;font-weight:700;color:#2563eb;font-family:'Inter',Arial,sans-serif;letter-spacing:1px;">Loading...</div>
    </div>
</div>
<style>
@keyframes vtu-spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
<script>
window.addEventListener('load', function() {
  var loader = document.getElementById('vtu-loader');
  if(loader) loader.style.display = 'none';
});
</script>