<?php
$pageTitle = 'Add / Edit Car';
include 'admin_header.php';
?>


<div class="container-card" id="carFormCard">
    <div class="card-heading">
        <span id="formTitle">Add New Car</span>
    </div>

    <p id="formMsg"></p>

    <form id="carForm" enctype="multipart/form-data" novalidate>
        <div class="form-grid">
            
            <div class="form-field">
                <label for="carName">Car Name *</label>
                <input type="text" id="carName" name="name" placeholder=" Toyota Camry">
                <span class="field-err" id="nameErr"></span>
            </div>
            
            <div class="form-field">
                <label for="carModel">Model / Year *</label>
                <input type="text" id="carModel" name="model" placeholder="2022">
                <span class="field-err" id="modelErr"></span>
            </div>
            
            <div class="form-field">
                <label for="carType">Car Type *</label>
                <select id="carType" name="type">
                    <option value="">-- Select Type --</option>
                    <option value="Private Car">Private Car</option>
                    <option value="Sports Car">Performance Car</option>
                    <option value="Microbus">Microbus</option>
                    <option value="Pick-up">Pick-up</option>
                    <option value="Other">Other</option>
                </select>
                <span class="field-err" id="typeErr"></span>
            </div>
            
            <div class="form-field">
                <label for="pricePerDay">Price Per Day (Tk) *</label>
                <input type="number" id="pricePerDay" name="price_per_day"  placeholder="6000">
                <span class="field-err" id="priceErr"></span>
            </div>
            
            <div class="form-field">
                <label for="availabilityStatus">Availability Status *</label>
                <select id="availabilityStatus" name="availability_status">
                    <option value="">-- Select Status --</option>
                    <option value="Available">Available</option>
                    <option value="Not Available">Not Available</option>
                </select>
                <span class="field-err" id="statusErr"></span>
            </div>
            
            <div class="form-field">
                <label for="carImage">Car Image (JPEG/PNG, max 2MB)</label>
                <input type="file" id="carImage" name="image" accept=".jpg,.jpeg,.png">
                <span class="field-err" id="imageErr"></span>
            </div>
            
            <div class="form-field span-2">
                <label for="description">Description</label>
                <textarea id="description" name="description" placeholder="Brief description of the car..."></textarea>
            </div>
        </div>

        <div class="btn-row">
            <button type="button" class="btn-1" id="saveCarBtn">Add Car</button>
            <button type="button" class="btn-cancel" id="cancelEditBtn" style="display:none">Cancel Edit</button>
        </div>
    </form>
</div>

<div id="toast-container"></div>

        </main>
    </div>
</div>

<script src="add_car.js"></script>
</body>
</html>
