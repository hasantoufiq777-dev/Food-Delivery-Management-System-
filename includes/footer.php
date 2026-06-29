        <!-- Premium Site Footer -->
        <footer class="site-footer">
            <div class="footer-grid">
                <div class="footer-brand-col">
                    <span class="footer-logo">🔥 FlameRoute</span>
                    <p class="footer-desc">Premium Food Delivery Management System. Connecting gourmet kitchens with hungry customers.</p>
                </div>
                <div class="footer-links-col">
                    <span class="footer-title">Company</span>
                    <ul class="footer-list">
                        <li><a href="#" class="footer-link">About Us</a></li>
                        <li><a href="#" class="footer-link">Careers</a></li>
                        <li><a href="#" class="footer-link">Partners</a></li>
                    </ul>
                </div>
                <div class="footer-links-col">
                    <span class="footer-title">Legal</span>
                    <ul class="footer-list">
                        <li><a href="#" class="footer-link">Privacy Policy</a></li>
                        <li><a href="#" class="footer-link">Terms of Service</a></li>
                        <li><a href="#" class="footer-link">Refund Policy</a></li>
                    </ul>
                </div>
                <div class="footer-contact-col">
                    <span class="footer-title">Support</span>
                    <ul class="footer-list">
                        <li><span class="footer-info-text">support@flameroute.com</span></li>
                        <li><span class="footer-info-text">+1 (555) 019-2834</span></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p class="footer-copy">&copy; <?= date('Y') ?> FlameRoute. All rights reserved.</p>
                <div class="footer-socials">
                    <a href="#" class="footer-social-link">Facebook</a>
                    <a href="#" class="footer-social-link">Twitter</a>
                    <a href="#" class="footer-social-link">Instagram</a>
                </div>
            </div>
        </footer>
    </main><!-- /.main-content -->

    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">⚠️ Confirm Delete</h3>
                <button class="modal-close" onclick="closeModal('deleteModal')"><?= icon('close', 20) ?></button>
            </div>
            <div class="modal-body">
                <p id="deleteModalMessage">Are you sure you want to delete this item? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('deleteModal')">Cancel</button>
                <a href="#" class="btn btn-danger" id="deleteConfirmBtn">Delete</a>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html>
