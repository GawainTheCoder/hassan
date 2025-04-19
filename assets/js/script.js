// assets/js/script.js
document.addEventListener('DOMContentLoaded', function() {
    console.log("Portfolio App script loaded.");
    
    // Fade in content
    const main = document.querySelector('main');
    if (main) {
        main.style.opacity = '0';
        main.style.transition = 'opacity 0.5s ease';
        setTimeout(() => {
            main.style.opacity = '1';
        }, 100);
    }
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href.startsWith('#')) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Close alert messages when clicking the X
    const closeButtons = document.querySelectorAll('.alert-close');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            this.parentElement.style.display = 'none';
        });
    });
    
    // Add confirm dialog to delete buttons
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
            }
        });
    });
    
    // Disable notice message after 5 seconds
    const notices = document.querySelectorAll('.notice');
    if (notices.length > 0) {
        setTimeout(() => {
            notices.forEach(notice => {
                notice.style.opacity = '0';
                notice.style.transition = 'opacity 0.5s ease';
                setTimeout(() => {
                    notice.style.display = 'none';
                }, 500);
            });
        }, 5000);
    }

    // Portfolio Showcase Filtering and Sorting
    const portfolioGrid = document.getElementById('portfolio-grid');
    console.log("Portfolio Grid Found:", portfolioGrid ? true : false);
    
    const categoryFilter = document.getElementById('category-filter');
    console.log("Category Filter Found:", categoryFilter ? true : false);
    
    const searchFilter = document.getElementById('search-filter');
    console.log("Search Filter Found:", searchFilter ? true : false);
    
    const sortOrder = document.getElementById('sort-order');
    console.log("Sort Order Found:", sortOrder ? true : false);
    
    const loadMoreBtn = document.getElementById('load-more');
    console.log("Load More Button Found:", loadMoreBtn ? true : false);
    
    const viewFilter = document.getElementById('view-filter');
    console.log("View Filter Found:", viewFilter ? true : false);
    
    if (portfolioGrid) {
        // Initial state - store all portfolio items
        const allItems = Array.from(portfolioGrid.querySelectorAll('.portfolio-item'));
        console.log("Portfolio Items Found:", allItems.length);
        
        // Log data attributes for debugging
        if (allItems.length > 0) {
            console.log("First Item Categories:", allItems.map(item => item.getAttribute('data-category')));
            console.log("First Item Owner:", allItems.map(item => item.getAttribute('data-owner')));
        }
        
        let visibleCount = allItems.length;
        const itemsPerPage = 6;
        
        // Function to filter and sort items
        function updatePortfolioItems() {
            // Get current filter values
            const selectedCategory = categoryFilter ? categoryFilter.value : '';
            const searchTerm = searchFilter ? searchFilter.value.toLowerCase().trim() : '';
            const sortType = sortOrder ? sortOrder.value : 'newest';
            const viewType = viewFilter ? viewFilter.value : 'all';
            
            console.log("Filtering with:", {
                category: selectedCategory,
                search: searchTerm,
                sort: sortType,
                view: viewType
            });
            
            // Filter items
            let filteredItems = allItems.filter(item => {
                // Category filter
                const categoryMatch = !selectedCategory || 
                    item.getAttribute('data-category') === selectedCategory;
                
                // View filter (for logged-in users)
                const viewMatch = viewType === 'all' || 
                    item.getAttribute('data-owner') === viewType;
                
                // Search term filter
                const itemTitle = item.querySelector('h3').textContent.toLowerCase();
                const itemDesc = item.querySelector('p').textContent.toLowerCase();
                const searchMatch = !searchTerm || 
                    itemTitle.includes(searchTerm) || 
                    itemDesc.includes(searchTerm);
                
                const result = categoryMatch && searchMatch && viewMatch;
                
                if (!result) {
                    console.log("Filtered out item:", {
                        title: itemTitle,
                        categoryMatch: categoryMatch,
                        viewMatch: viewMatch,
                        searchMatch: searchMatch,
                        itemCategory: item.getAttribute('data-category'),
                        itemOwner: item.getAttribute('data-owner')
                    });
                }
                
                return result;
            });
            
            console.log("Filtered Items Count:", filteredItems.length);
            
            // Sort items
            console.log("Sorting items...");
            filteredItems.sort((a, b) => {
                const titleA = a.querySelector('h3').textContent;
                const titleB = b.querySelector('h3').textContent;
                // Ensure timestamp is parsed as integer, default to 0 if invalid
                const timeA = parseInt(a.getAttribute('data-timestamp'), 10) || 0;
                const timeB = parseInt(b.getAttribute('data-timestamp'), 10) || 0;
                
                let compareResult = 0;
                switch(sortType) {
                    case 'name-asc':
                        compareResult = titleA.localeCompare(titleB);
                        break;
                    case 'name-desc':
                        compareResult = titleB.localeCompare(titleA);
                        break;
                    case 'oldest':
                        compareResult = timeA - timeB;
                        break;
                    case 'newest':
                    default:
                        compareResult = timeB - timeA;
                        break;
                }
                
                // Log comparison details - remove in production
                if (sortType === 'newest' || sortType === 'oldest') {
                    console.log(`  Comparing ${titleA} (${timeA}) and ${titleB} (${timeB}) | Sort: ${sortType} | Result: ${compareResult}`);
                }
                
                return compareResult;
            });
            console.log("Sorting complete.");
            
            // --- DOM Update Logic --- 
            // Clear the grid before appending sorted items
            portfolioGrid.innerHTML = ''; 

            // Append sorted and filtered items back to the grid (up to visibleCount)
            const itemsToShow = filteredItems.slice(0, visibleCount);
            console.log("Appending Items Count:", itemsToShow.length);
            itemsToShow.forEach(item => {
                portfolioGrid.appendChild(item); // Re-add item to the grid in sorted order
                // Ensure item is visible (it should be by default after appendChild)
                // item.style.display = ''; // This line is likely unnecessary now
            });
            // --- End DOM Update Logic --- 
            
            // Update load more button visibility
            if (loadMoreBtn) {
                loadMoreBtn.style.display = filteredItems.length > visibleCount ? '' : 'none';
            }
            
            // Show "No items found" message if no results
            let emptyMessage = document.querySelector('.portfolio-empty-message');
            
            if (filteredItems.length === 0) {
                if (!emptyMessage) {
                    emptyMessage = document.createElement('div');
                    emptyMessage.className = 'portfolio-empty-message';
                    emptyMessage.textContent = 'No portfolio items match your filters.';
                    portfolioGrid.parentNode.insertBefore(emptyMessage, portfolioGrid.nextSibling);
                }
                emptyMessage.style.display = '';
            } else if (emptyMessage) {
                emptyMessage.style.display = 'none';
            }
        }
        
        // Event listeners for filters and sorting
        if (categoryFilter) {
            categoryFilter.addEventListener('change', function() {
                console.log("Category changed to:", this.value);
                visibleCount = itemsPerPage; // Reset visible count on filter change
                updatePortfolioItems();
            });
        }
        
        if (searchFilter) {
            searchFilter.addEventListener('input', function() {
                console.log("Search changed to:", this.value);
                visibleCount = itemsPerPage; // Reset visible count on search
                updatePortfolioItems();
            });
        }
        
        if (sortOrder) {
            sortOrder.addEventListener('change', function() {
                console.log("Sort changed to:", this.value);
                updatePortfolioItems();
            });
        }
        
        // New event listener for view filter (logged-in users)
        if (viewFilter) {
            viewFilter.addEventListener('change', function() {
                console.log("View changed to:", this.value);
                visibleCount = itemsPerPage; // Reset visible count on view change
                updatePortfolioItems();
            });
        }
        
        // Load more button functionality
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', function() {
                console.log("Load more clicked, increasing visible count from", visibleCount, "to", visibleCount + itemsPerPage);
                visibleCount += itemsPerPage;
                updatePortfolioItems();
            });
        }
        
        // Initial call to setup the view
        console.log("Initial portfolio setup");
        updatePortfolioItems();

        // Check if portfolio items have the necessary data attributes
        if (allItems.length > 0) {
            const dataAttributes = allItems.map(item => {
                return {
                    category: item.getAttribute('data-category'),
                    owner: item.getAttribute('data-owner')
                };
            });
            console.log('Portfolio items data attributes:', dataAttributes);

            // Check for missing attributes that would cause filtering issues
            const missingAttributes = dataAttributes.filter(attr => !attr.category || !attr.owner);
            if (missingAttributes.length > 0) {
                console.warn('⚠️ Some portfolio items are missing data attributes required for filtering:', missingAttributes);
            }
        }
    } else {
        console.warn("Portfolio grid not found, filtering/sorting functionality disabled");
    }
});
