</div>
</div>

 
<!-- Admin Footer -->
<footer class="footer admin-footer mt-5" style="background:#fff;color:#2563eb;text-align:center;padding:1.5rem 0;font-family:'Inter',Arial,sans-serif;letter-spacing:2px;border-top:4px solid #2563eb;">
    <div class="container">
        <?php
        // Fetch site name from settings table for display in admin footer
        $siteName = 'VTUNaija';
        try {
            $db = DB::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT site_name FROM settings LIMIT 1");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && !empty($row['site_name'])) {
                $siteName = $row['site_name'];
            }
        } catch (Exception $e) {
            // fallback to default
        }
        ?>
        <span style="font-size:1.1rem;">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($siteName); ?> Admin Panel. All rights reserved.</span>
        <div style="margin-top:0.5rem;font-size:0.95rem;color:#6b7280;">
            <span>Powered by <?php echo htmlspecialchars($siteName); ?> Technologies</span>
        </div>
    </div>
</footer>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>