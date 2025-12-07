<!-- Modal Structure -->
<div class="modal-overlay" id="modalOverlay"></div>

<!-- Umbrella Cottage Modal -->
<div class="modal" id="modal-umbrella">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal()">&times;</button>
        <div class="modal-header">
            <h2>Umbrella Cottage</h2>
            <p class="modal-subtitle">Perfect for small families</p>
        </div>
        <div class="modal-body">
            <div class="modal-info">
                <p class="modal-capacity"><i class="fas fa-users"></i> Capacity: 4-5 pax</p>
                <div class="modal-options">
                    <h4>Available Options:</h4>
                    <div class="option-item">
                        <span>Public Area</span>
                        <strong>₱600</strong>
                    </div>
                    <div class="option-item">
                        <span>Semi-Private Area</span>
                        <strong>₱700</strong>
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button class="modal-btn modal-btn-primary" onclick="selectService(1, 'Umbrella Cottage - Public Area', 'Public Area', 600)">
                    Select Public Area (₱600)
                </button>
                <button class="modal-btn modal-btn-primary" onclick="selectService(2, 'Umbrella Cottage - Semi-Private Area', 'Semi-Private Area', 700)">
                    Select Semi-Private (₱700)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Family Cottage Modal -->
<div class="modal" id="modal-family">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal()">&times;</button>
        <div class="modal-header">
            <h2>Family Cottage</h2>
            <p class="modal-subtitle">Ideal for large families</p>
        </div>
        <div class="modal-body">
            <div class="modal-info">
                <p class="modal-capacity"><i class="fas fa-users"></i> Capacity: 10-15 pax</p>
                <div class="modal-options">
                    <h4>Available Options:</h4>
                    <div class="option-item">
                        <span>Public Area</span>
                        <strong>₱900</strong>
                    </div>
                    <div class="option-item">
                        <span>Semi-Private Area</span>
                        <strong>₱1,000</strong>
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button class="modal-btn modal-btn-primary" onclick="selectService(3, 'Family Cottage - Public Area', 'Public Area', 900)">
                    Select Public Area (₱900)
                </button>
                <button class="modal-btn modal-btn-primary" onclick="selectService(4, 'Family Cottage - Semi-Private Area', 'Semi-Private Area', 1000)">
                    Select Semi-Private (₱1,000)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Barkada Cottage Modal -->
<div class="modal" id="modal-barkada">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal()">&times;</button>
        <div class="modal-header">
            <h2>Barkada Cottage</h2>
            <p class="modal-subtitle">Great for groups</p>
        </div>
        <div class="modal-body">
            <div class="modal-info">
                <p class="modal-capacity"><i class="fas fa-users"></i> Capacity: 20-30 pax</p>
                <div class="modal-options">
                    <h4>Available Options:</h4>
                    <div class="option-item">
                        <span>Public Area</span>
                        <strong>₱1,300</strong>
                    </div>
                    <div class="option-item">
                        <span>Semi-Private Area</span>
                        <strong>₱1,500</strong>
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button class="modal-btn modal-btn-primary" onclick="selectService(5, 'Barkada Cottage - Public Area', 'Public Area', 1300)">
                    Select Public Area (₱1,300)
                </button>
                <button class="modal-btn modal-btn-primary" onclick="selectService(6, 'Barkada Cottage - Semi-Private Area', 'Semi-Private Area', 1500)">
                    Select Semi-Private (₱1,500)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Silong Modal -->
<div class="modal" id="modal-silong">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal()">&times;</button>
        <div class="modal-header">
            <h2>Silong</h2>
            <p class="modal-subtitle">Spacious for large gatherings</p>
        </div>
        <div class="modal-body">
            <div class="modal-info">
                <p class="modal-capacity"><i class="fas fa-users"></i> Capacity: 30-40 pax</p>
                <div class="modal-options">
                    <h4>Available Options:</h4>
                    <div class="option-item">
                        <span>Standard</span>
                        <strong>₱2,000</strong>
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button class="modal-btn modal-btn-primary" onclick="selectService(7, 'Silong', 'Standard', 2000)">
                    Select Silong (₱2,000)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Casa Ernesto Modal -->
<div class="modal" id="modal-ernesto">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal()">&times;</button>
        <div class="modal-header">
            <h2>Casa Ernesto</h2>
            <p class="modal-subtitle">Luxury suite for up to 18 guests</p>
        </div>
        <div class="modal-body">
            <div class="modal-info">
                <p class="modal-capacity"><i class="fas fa-users"></i> Capacity: 18 Pax</p>
                <div class="modal-amenities">
                    <h4>Amenities:</h4>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Airconditioned Room</li>
                        <li><i class="fas fa-check-circle"></i> Single & Double Deck Beds</li>
                        <li><i class="fas fa-check-circle"></i> Dining Area</li>
                        <li><i class="fas fa-check-circle"></i> Comfort Room</li>
                        <li><i class="fas fa-check-circle"></i> Patio (Outdoor Table)</li>
                    </ul>
                </div>
                <div class="modal-options">
                    <h4>Available Options:</h4>
                    <div class="option-item">
                        <span>Day Tour (7am to 5pm)</span>
                        <strong>₱6,500</strong>
                    </div>
                    <div class="option-item">
                        <span>Night Tour (7pm to 5am)</span>
                        <strong>₱7,500</strong>
                    </div>
                    <div class="option-item">
                        <span>Overnight Stay (2pm to 11am)</span>
                        <strong>₱8,500</strong>
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button class="modal-btn modal-btn-primary" onclick="selectService(8, 'Casa Ernesto - Day Tour', 'Day Tour', 6500)">
                    Select Day Tour (₱6,500)
                </button>
                <button class="modal-btn modal-btn-primary" onclick="selectService(9, 'Casa Ernesto - Night Tour', 'Night Tour', 7500)">
                    Select Night Tour (₱7,500)
                </button>
                <button class="modal-btn modal-btn-primary" onclick="selectService(10, 'Casa Ernesto - Overnight', 'Overnight', 8500)">
                    Select Overnight (₱8,500)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Casa Ma. Elena Modal -->
<div class="modal" id="modal-elena">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal()">&times;</button>
        <div class="modal-header">
            <h2>Casa Ma. Elena</h2>
            <p class="modal-subtitle">Family room for up to 8 guests</p>
        </div>
        <div class="modal-body">
            <div class="modal-info">
                <p class="modal-capacity"><i class="fas fa-users"></i> Capacity: 8 Pax</p>
                <div class="modal-amenities">
                    <h4>Amenities:</h4>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> 2 Airconditioned Bedrooms</li>
                        <li><i class="fas fa-check-circle"></i> Double Deck Beds</li>
                        <li><i class="fas fa-check-circle"></i> Dining Area</li>
                        <li><i class="fas fa-check-circle"></i> Comfort Room</li>
                    </ul>
                </div>
                <div class="modal-options">
                    <h4>Available Options:</h4>
                    <div class="option-item">
                        <span>Day Tour (7am to 5pm)</span>
                        <strong>₱4,000</strong>
                    </div>
                    <div class="option-item">
                        <span>Night Tour (7pm to 5am)</span>
                        <strong>₱5,000</strong>
                    </div>
                    <div class="option-item">
                        <span>Overnight Stay (2pm to 11am)</span>
                        <strong>₱6,500</strong>
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button class="modal-btn modal-btn-primary" onclick="selectService(11, 'Casa Ma. Elena - Day Tour', 'Day Tour', 4000)">
                    Select Day Tour (₱4,000)
                </button>
                <button class="modal-btn modal-btn-primary" onclick="selectService(12, 'Casa Ma. Elena - Night Tour', 'Night Tour', 5000)">
                    Select Night Tour (₱5,000)
                </button>
                <button class="modal-btn modal-btn-primary" onclick="selectService(13, 'Casa Ma. Elena - Overnight', 'Overnight', 6500)">
                    Select Overnight (₱6,500)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Casa Edmundo Modal -->
<div class="modal" id="modal-edmundo">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal()">&times;</button>
        <div class="modal-header">
            <h2>Casa Edmundo</h2>
            <p class="modal-subtitle">Cozy room for up to 5 guests</p>
        </div>
        <div class="modal-body">
            <div class="modal-info">
                <p class="modal-capacity"><i class="fas fa-users"></i> Capacity: 5 Pax</p>
                <div class="modal-amenities">
                    <h4>Amenities:</h4>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Airconditioned Bedroom</li>
                        <li><i class="fas fa-check-circle"></i> Double Deck and Single Bed</li>
                        <li><i class="fas fa-check-circle"></i> Dining Area</li>
                        <li><i class="fas fa-check-circle"></i> Comfort Room</li>
                    </ul>
                </div>
                <div class="modal-options">
                    <h4>Available Options:</h4>
                    <div class="option-item">
                        <span>Day Tour (7am to 5pm)</span>
                        <strong>₱3,000</strong>
                    </div>
                    <div class="option-item">
                        <span>Night Tour (7pm to 5am)</span>
                        <strong>₱4,000</strong>
                    </div>
                    <div class="option-item">
                        <span>Overnight Stay (2pm to 11am)</span>
                        <strong>₱5,000</strong>
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button class="modal-btn modal-btn-primary" onclick="selectService(14, 'Casa Edmundo - Day Tour', 'Day Tour', 3000)">
                    Select Day Tour (₱3,000)
                </button>
                <button class="modal-btn modal-btn-primary" onclick="selectService(15, 'Casa Edmundo - Night Tour', 'Night Tour', 4000)">
                    Select Night Tour (₱4,000)
                </button>
                <button class="modal-btn modal-btn-primary" onclick="selectService(16, 'Casa Edmundo - Overnight', 'Overnight', 5000)">
                    Select Overnight (₱5,000)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Standard Cuarto Modal -->
<div class="modal" id="modal-standard">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal()">&times;</button>
        <div class="modal-header">
            <h2>Standard Cuarto</h2>
            <p class="modal-subtitle">Affordable comfort</p>
        </div>
        <div class="modal-body">
            <div class="modal-info">
                <div class="modal-options">
                    <h4>Available Options:</h4>
                    <div class="option-item">
                        <span>6 Hours</span>
                        <strong>₱500</strong>
                    </div>
                    <div class="option-item">
                        <span>12 Hours</span>
                        <strong>₱1,000</strong>
                    </div>
                    <div class="option-item">
                        <span>24 Hours</span>
                        <strong>₱2,000</strong>
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button class="modal-btn modal-btn-primary" onclick="selectService(17, 'Standard Cuarto - 6 Hours', '6 Hours', 500)">
                    Select 6 Hours (₱500)
                </button>
                <button class="modal-btn modal-btn-primary" onclick="selectService(18, 'Standard Cuarto - 12 Hours', '12 Hours', 1000)">
                    Select 12 Hours (₱1,000)
                </button>
                <button class="modal-btn modal-btn-primary" onclick="selectService(19, 'Standard Cuarto - 24 Hours', '24 Hours', 2000)">
                    Select 24 Hours (₱2,000)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Deluxe Cuarto Modal -->
<div class="modal" id="modal-deluxe">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal()">&times;</button>
        <div class="modal-header">
            <h2>Deluxe Cuarto</h2>
            <p class="modal-subtitle">Premium experience</p>
        </div>
        <div class="modal-body">
            <div class="modal-info">
                <div class="modal-options">
                    <h4>Available Options:</h4>
                    <div class="option-item">
                        <span>6 Hours</span>
                        <strong>₱700</strong>
                    </div>
                    <div class="option-item">
                        <span>12 Hours</span>
                        <strong>₱1,400</strong>
                    </div>
                    <div class="option-item">
                        <span>24 Hours</span>
                        <strong>₱2,800</strong>
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button class="modal-btn modal-btn-primary" onclick="selectService(20, 'Deluxe Cuarto - 6 Hours', '6 Hours', 700)">
                    Select 6 Hours (₱700)
                </button>
                <button class="modal-btn modal-btn-primary" onclick="selectService(21, 'Deluxe Cuarto - 12 Hours', '12 Hours', 1400)">
                    Select 12 Hours (₱1,400)
                </button>
                <button class="modal-btn modal-btn-primary" onclick="selectService(22, 'Deluxe Cuarto - 24 Hours', '24 Hours', 2800)">
                    Select 24 Hours (₱2,800)
                </button>
            </div>
        </div>
    </div>
</div>