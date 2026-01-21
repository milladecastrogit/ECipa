    </main>

    <footer>
        <p>&copy; 2024 E-CIPA - Sistema de Eleição Digital para CIPA. Todos os direitos reservados.</p>
    </footer>

    <script>
        // Menu toggle para mobile
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.menu-toggle');
            const sidebar = document.querySelector('aside');
            
            if (menuToggle) {
                menuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('open');
                });
            }
        });
    </script>
</body>
</html>
