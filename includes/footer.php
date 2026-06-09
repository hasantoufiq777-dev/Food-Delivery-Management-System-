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
