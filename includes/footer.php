<footer class="bg-[#0a1a2b] py-12 mt-auto">
  <div class="container">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
      <div>
        <h3 class="text-white text-lg font-bold mb-4">Grand Horizon</h3>
        <p class="text-gray-400 text-sm">Luxury accommodations with world-class amenities in the heart of the city.</p>
        <div class="flex space-x-4 mt-4">
          <a href="#" class="text-gray-400 hover:text-blue-400 transition" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="text-gray-400 hover:text-blue-400 transition" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
          <a href="#" class="text-gray-400 hover:text-blue-400 transition" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="#" class="text-gray-400 hover:text-blue-400 transition" aria-label="TripAdvisor"><i class="fab fa-tripadvisor"></i></a>
        </div>
      </div>
      <div>
        <h3 class="text-white text-lg font-bold mb-4">Quick Links</h3>
        <ul class="space-y-2">
          <li><a href="index.php" class="text-gray-400 hover:text-blue-400 text-sm transition">Home</a></li>
          <li><a href="rooms.php" class="text-gray-400 hover:text-blue-400 text-sm transition">Rooms & Suites</a></li>
          <li><a href="gallery.php" class="text-gray-400 hover:text-blue-400 text-sm transition">Photo Gallery</a></li>
          <li><a href="news.php" class="text-gray-400 hover:text-blue-400 text-sm transition">Hotel News</a></li>
          <li><a href="contact.php" class="text-gray-400 hover:text-blue-400 text-sm transition">Contact Us</a></li>
        </ul>
      </div>
      <div>
        <h3 class="text-white text-lg font-bold mb-4">Contact Us</h3>
        <ul class="space-y-2 text-gray-400 text-sm">
          <li class="flex items-start">
            <i class="fas fa-map-marker-alt mt-1 mr-2 text-blue-400"></i>
            <span>123 Hotel Street, City, Country</span>
          </li>
          <li class="flex items-center">
            <i class="fas fa-phone-alt mr-2 text-blue-400"></i>
            <span>+1 234 567 8900</span>
          </li>
          <li class="flex items-center">
            <i class="fas fa-envelope mr-2 text-blue-400"></i>
            <span>info@grandhorizon.com</span>
          </li>
          <li class="flex items-center">
            <i class="fas fa-headset mr-2 text-blue-400"></i>
            <span>24/7 Customer Support</span>
          </li>
        </ul>
      </div>
      <div>
        <h3 class="text-white text-lg font-bold mb-4">Opening Hours</h3>
        <ul class="space-y-2 text-gray-400 text-sm">
          <li class="flex justify-between">
            <span>Monday - Friday:</span>
            <span>24 hours</span>
          </li>
          <li class="flex justify-between">
            <span>Saturday - Sunday:</span>
            <span>24 hours</span>
          </li>
        </ul>
      </div>
    </div>
    <div class="border-t border-gray-800 pt-6 flex flex-col md:flex-row justify-between items-center text-gray-400 text-sm">
      <p>&copy; <?php echo date('Y'); ?> Grand Horizon Hotel. All rights reserved.</p>
      <div class="flex space-x-4 mt-4 md:mt-0">
        <a href="privacy.php" class="hover:text-blue-400 transition">Privacy Policy</a>
        <a href="terms.php" class="hover:text-blue-400 transition">Terms of Service</a>
        <a href="sitemap.php" class="hover:text-blue-400 transition">Sitemap</a>
      </div>
    </div>
  </div>
</footer>

<button id="back-to-top" class="fixed bottom-8 right-8 w-12 h-12 rounded-full flex items-center justify-center bg-gradient-to-br from-primary to-blue-600 shadow-lg transition-all opacity-0 invisible hover:scale-105">
  <i class="fas fa-arrow-up"></i>
</button>

<script>
  const backToTopButton = document.getElementById('back-to-top');
  
  if (backToTopButton) {
    window.addEventListener('scroll', () => {
      if (window.pageYOffset > 300) {
        backToTopButton.classList.remove('hidden', 'opacity-0', 'invisible');
        backToTopButton.classList.add('flex', 'opacity-100', 'visible');
      } else {
        backToTopButton.classList.add('opacity-0', 'invisible');
        backToTopButton.classList.remove('opacity-100', 'visible');
      }
    });
    
    backToTopButton.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }


function toggleUserDropdown() {
  const userDropdown = document.getElementById('user-dropdown');
  userDropdown.classList.toggle('hidden');
}

document.addEventListener('click', (e) => {
  const userMenuBtn = document.getElementById('user-menu-btn');
  const userDropdown = document.getElementById('user-dropdown');
  
  if (userMenuBtn && userDropdown && !userMenuBtn.contains(e.target)) {
    userDropdown.classList.add('hidden');
  }
});
function toggleMobileMenu() {
  const mobileMenu = document.getElementById('mobile-menu');
  mobileMenu.classList.toggle('translate-x-full');
  mobileMenu.classList.toggle('translate-x-0');
  
  if (mobileMenu.classList.contains('translate-x-0')) {
    document.body.style.overflow = 'hidden';
  } else {
    document.body.style.overflow = 'auto';
  }
}
</script>