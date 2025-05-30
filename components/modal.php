<div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <div class="modal-body">
                <div class="modal-image-container">
                    <img id="modalProductImage" src="" alt="Product Image">
                </div>
                <div class="modal-form-container">
                    <h2 id="modalProductName">Product Name</h2>
                    <p id="modalProductCategory" class="modal-category-tag">Category</p>
                    <p id="modalProductPrice" class="modal-price">Rp 0</p>
                    <div id="modalProductDescription">
                        <p>Loading description...</p> </div>
                    <form id="purchaseForm">
                        <input type="hidden" id="modalProductId" name="productId">
                        <div class="form-group">
                            <label for="quantity">Quantity:</label>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" class="form-control">
                        </div>
                        <button type="submit" class="submit-button">Add to Cart</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('productModal');
    const closeButton = document.querySelector('.close-button');
    const productCards = document.querySelectorAll('.card'); //

    const modalProductImage = document.getElementById('modalProductImage');
    const modalProductName = document.getElementById('modalProductName');
    const modalProductCategory = document.getElementById('modalProductCategory');
    const modalProductPrice = document.getElementById('modalProductPrice');
    const modalProductDescription = document.getElementById('modalProductDescription');
    const modalProductIdInput = document.getElementById('modalProductId');

    productCards.forEach(card => {
        card.addEventListener('click', function () {
            const productImageSrc = card.querySelector('.card-image img').src; //
            const productName = card.querySelector('.card-title h3').textContent; //
            const productPriceText = card.querySelector('.card-title p').textContent; //
            const productCategory = card.dataset.category || 'N/A'; 
            const productId = card.querySelector('.card-title h2').textContent;


            modalProductImage.src = productImageSrc;
            modalProductImage.alt = productName;
            modalProductName.textContent = productName;
            modalProductCategory.textContent = productCategory.charAt(0).toUpperCase() + productCategory.slice(1);
            modalProductPrice.textContent = productPriceText;
            modalProductIdInput.value = productId;  

            fetchProductDescription(productId); 

            modal.style.display = 'block';
        });
    });

    function closeModal() {
        modal.style.animation = 'fadeOut 0.3s ease-out forwards';  
        setTimeout(() => {
            modal.style.display = 'none';
            modal.style.animation = '';  
        }, 300);  
    }

    closeButton.addEventListener('click', closeModal);

    window.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    const purchaseForm = document.getElementById('purchaseForm');
    purchaseForm.addEventListener('submit', function(event) {
        event.preventDefault(); 
        const productId = document.getElementById('modalProductId').value;
        const quantity = document.getElementById('quantity').value;
        alert(`Product ID: ${productId}\nQuantity: ${quantity}\n\nForm submitted! (Implement actual submission logic)`);
        closeModal();
    });

    function fetchProductDescription(productId) {
        modalProductDescription.innerHTML = '<p>Loading full description...</p>';
        setTimeout(() => {
            let description = "This is a detailed description of the product. It highlights key features, benefits, and materials used. Perfect for everyday use and special occasions alike. More details would be fetched from your database based on the product ID: " + productId;
            
            if (productId.includes("hoodie")) {  
                description = "This premium hoodie offers both comfort and style. Made from 100% organic cotton, it's soft to the touch and durable. Features a double-lined hood, front pouch pocket, and ribbed cuffs and hem. Available in various colors.";
            } else if (productId.includes("t-shirt")){
                description = "A classic crew neck t-shirt crafted from breathable cotton. Features a relaxed fit for maximum comfort. Ideal for layering or wearing on its own. Check out the different color options available.";
            }

            modalProductDescription.innerHTML = `<p>${description}</p>`;
        }, 500); 
    }
});
</script>