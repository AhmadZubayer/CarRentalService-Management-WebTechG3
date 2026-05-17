



var currentCarId = null;

function showToast(message, type) {
    var container = document.getElementById('toast-container');
    var toast = document.createElement('div');
    toast.className = 'toast ' + (type || 'success');
    toast.textContent = message;
    container.appendChild(toast);
    setTimeout(function() {
        if (toast.parentNode) toast.parentNode.removeChild(toast);
    }, 3500);
}


const urlParams = new URLSearchParams(window.location.search);
const editId = urlParams.get('edit_id');

if (editId) {
    currentCarId = editId;
    document.getElementById('formTitle').textContent = 'Edit Car';
    document.getElementById('saveCarBtn').textContent = 'Update Car';
    document.getElementById('cancelEditBtn').style.display = 'inline-block';

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '../../ajax_handler.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            var car = JSON.parse(xhr.responseText);
            document.getElementById('carName').value            = car.name;
            document.getElementById('carModel').value           = car.model;
            document.getElementById('carType').value            = car.type;
            document.getElementById('pricePerDay').value        = car.price_per_day;
            document.getElementById('description').value        = car.description || '';
            document.getElementById('availabilityStatus').value = car.availability_status;
        }
    };
    xhr.send('module=car&action=getOne&id=' + currentCarId);
}

document.getElementById('cancelEditBtn').addEventListener('click', function() {
    window.location.href = 'manage_cars.php';
});

document.getElementById('saveCarBtn').addEventListener('click', function() {
    var valid = true;
    document.querySelectorAll('.field-err').forEach(function(el){ el.textContent = ''; });
    document.getElementById('formMsg').textContent = '';

    var name   = document.getElementById('carName').value.trim();
    var model  = document.getElementById('carModel').value.trim();
    var type   = document.getElementById('carType').value;
    var price  = document.getElementById('pricePerDay').value.trim();
    var status = document.getElementById('availabilityStatus').value;
    var imgFile= document.getElementById('carImage').files[0];

    if (!name) {
        document.getElementById('nameErr').textContent = 'Car name is required.';
        valid = false;
    }
    if (!model) {
        document.getElementById('modelErr').textContent = 'Car model is required.';
        valid = false;
    }
    if (!type) {
        document.getElementById('typeErr').textContent = 'Please select a car type.';
        valid = false;
    }
    if (!price || isNaN(price) || parseFloat(price) <= 0) {
        document.getElementById('priceErr').textContent = 'Price must be a positive number.';
        valid = false;
    }
    if (!status) {
        document.getElementById('statusErr').textContent = 'Please select availability status.';
        valid = false;
    }
    if (!currentCarId && !imgFile) {
        document.getElementById('imageErr').textContent = 'Car image is required.';
        valid = false;
    }
    if (imgFile) {
        var allowedTypes = ['image/jpeg', 'image/png'];
        if (!allowedTypes.includes(imgFile.type)) {
            document.getElementById('imageErr').textContent = 'Image must be JPEG or PNG.';
            valid = false;
        } else if (imgFile.size > 2 * 1024 * 1024) {
            document.getElementById('imageErr').textContent = 'Image must be 2MB or smaller.';
            valid = false;
        }
    }

    if (!valid) return;

    var formData = new FormData();
    formData.append('module', 'car');
    formData.append('action', currentCarId ? 'update' : 'add');
    if (currentCarId) formData.append('id', currentCarId);
    formData.append('name', name);
    formData.append('model', model);
    formData.append('type', type);
    formData.append('price_per_day', price);
    formData.append('description', document.getElementById('description').value);
    formData.append('availability_status', status);
    if (imgFile) formData.append('image', imgFile);

    var btn = document.getElementById('saveCarBtn');
    btn.disabled = true;
    btn.textContent = 'Saving...';

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '../../ajax_handler.php', true);
    xhr.onload = function() {
        btn.disabled = false;
        btn.textContent = currentCarId ? 'Update Car' : 'Add Car';

        if (xhr.status >= 200 && xhr.status < 300) {
            var data = JSON.parse(xhr.responseText);
            if (data.status === 'success') {
                showToast(data.message, 'success');
                setTimeout(function() {
                    window.location.href = 'manage_cars.php';
                }, 1000);
            } else {
                document.getElementById('formMsg').textContent = data.message;
            }
        }
    };
    xhr.send(formData);
});
