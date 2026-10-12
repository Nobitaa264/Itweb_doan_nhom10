<?php
/*
 * Tệp: footer.php
 * Chức năng: Phần cuối dùng chung cho các trang thành viên.
 * Hiển thị thông tin bản quyền và liên hệ của nhóm.
 * File được gọi bằng require từ trang PHP thành viên.
 * Cách kiểm tra: mở trang cá nhân và kiểm tra footer.
 */
?>

        </div>

    </main>


    <footer class="chan-trang">

        <div class="bao">

            <p>
                © 2026 Nhóm 10 — Khoa Toán – Tin
            </p>

            <p>
                Website được xây dựng cho học phần
                Thiết kế và Lập trình Web.
            </p>

            <p>
                <a href="<?= e($goc) ?>lien-he.html">
                    Liên hệ
                </a>
            </p>

        </div>

    </footer>

</div>


<?php if (!empty($jsCaNhan)): ?>

    <script src="<?= e($jsCaNhan) ?>"></script>

<?php endif; ?>


</body>

</html>