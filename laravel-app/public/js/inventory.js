/* ═══════════════════════════════════════════════════════════
   inventory.js  —  zone/category_id based, may loading overlay na,
   may low-stock preview panel na rin
═══════════════════════════════════════════════════════════ */

/* ─── LOADING OVERLAY HELPERS ──────────────────────────────── */
function showLoading(text) {
    var overlay = document.getElementById('loadingOverlay');
    var textEl  = document.getElementById('loadingText');
    if (textEl) textEl.textContent = text || 'Processing…';
    if (overlay) overlay.classList.add('active');
}
function hideLoading() {
    var overlay = document.getElementById('loadingOverlay');
    if (overlay) overlay.classList.remove('active');
}

/* ─── TOAST (gumagamit na ng #toastStack) ─────────────────── */
function showToast(msg, type) {
    var stack = document.getElementById('toastStack');
    if (!stack) return;

    var toast = document.createElement('div');
    toast.className = 'toast toast-' + (type === 'error' ? 'error' : 'success');

    var iconClass = type === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check';
    var title      = type === 'error' ? 'Error' : 'Success';

    toast.innerHTML =
        '<span class="toast-icon"><i class="fas ' + iconClass + '"></i></span>' +
        '<span class="toast-body">' +
            '<span class="toast-title">' + title + '</span>' +
            '<span class="toast-msg">' + escHtml(msg) + '</span>' +
        '</span>' +
        '<button type="button" class="toast-close">&times;</button>' +
        '<span class="toast-bar"></span>';

    stack.appendChild(toast);

    function remove() {
        toast.classList.add('hide');
        setTimeout(function () { toast.remove(); }, 280);
    }

    toast.querySelector('.toast-close').addEventListener('click', remove);
    setTimeout(remove, 3000);
}

function statusBadge(stock, threshold) {
    var t = parseInt(threshold) || 20;
    return parseInt(stock) <= t
        ? '<span class="status-badge status-low">Low Stock</span>'
        : '<span class="status-badge status-good">In Stock</span>';
}

function post(url, data, callback, loadingMsg) {
    showLoading(loadingMsg || 'Processing…');
    var fd = new FormData();
    Object.keys(data).forEach(function (k) { fd.append(k, data[k] === null ? '' : data[k]); });
    fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    fetch(url, { method: 'POST', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (res) { hideLoading(); callback(res); })
        .catch(function () { hideLoading(); showToast('Server error. Check your connection.', 'error'); });
}

/* ─── PAGINATION STATE ────────────────────────────────────── */
var ROWS_PER_PAGE = 10;
var currentPage   = 1;

/* ─── LOW STOCK PREVIEW STATE ──────────────────────────────── */
var lowStockItemsCache = [];

/* ─── METRICS ──────────────────────────────────────────────── */
function loadMetrics() {
    var rows  = document.querySelectorAll('#inventoryTable tbody tr');
    var total = 0, low = 0, stocks = 0;
    var lowItems = [];

    rows.forEach(function (r) {
        if (r.dataset.match === '0') return;
        total++;
        var cell      = r.children[2].textContent.trim();
        var s         = parseInt(r.children[2].textContent) || 0;
        var tracked   = r.dataset.tracked === '1';
        var threshold = parseInt(r.dataset.threshold) || 20;
        stocks += s;

        if (tracked && cell !== '—' && s <= threshold) {
            low++;
            lowItems.push({
                id:    r.dataset.id,
                name:  r.dataset.name,
                cat:   r.dataset.cat,
                zone:  r.dataset.zone,
                stock: s
            });
        }
    });

    document.getElementById('totalProducts').textContent = total;
    document.getElementById('lowStocks').textContent     = low;
    document.getElementById('totalStocks').textContent   = stocks;

    lowItems.sort(function (a, b) { return a.stock - b.stock; });
    lowStockItemsCache = lowItems;
    renderLowStockPreview();
}

/* ─── LOW STOCK PREVIEW ────────────────────────────────────── */
function renderLowStockPreview() {
    var list = document.getElementById('lowStockPreviewList');
    if (!list) return;

    if (lowStockItemsCache.length === 0) {
        list.innerHTML = '<div class="low-stock-preview-empty">🎉 No low stock items right now.</div>';
        return;
    }

    list.innerHTML = lowStockItemsCache.map(function (item) {
        return '<div class="low-stock-preview-item" data-jump-id="' + item.id + '">' +
                    '<span class="lsp-name">' + escHtml(item.name) +
                        '<span class="lsp-cat">' + escHtml(item.cat) + ' · ' + escHtml(item.zone) + '</span>' +
                    '</span>' +
                    '<span class="lsp-qty">' + item.stock + ' left</span>' +
                '</div>';
    }).join('');
}

function toggleLowStockPreview(forceOpen) {
    var panel = document.getElementById('lowStockPreview');
    var card  = document.getElementById('lowStockCard');
    var open  = typeof forceOpen === 'boolean' ? forceOpen : !panel.classList.contains('active');

    panel.classList.toggle('active', open);
    card.classList.toggle('open', open);
}

function jumpToProductRow(id) {
    // Clear active filters so the row is guaranteed to be visible
    document.getElementById('searchInput').value    = '';
    document.getElementById('zoneFilter').value     = 'all';
    document.getElementById('categoryFilter').value = 'all';
    computeMatches();

    var matched = Array.from(document.querySelectorAll('#inventoryTable tbody tr'))
        .filter(function (r) { return r.dataset.match !== '0'; });
    var idx = matched.findIndex(function (r) { return r.dataset.id === id; });
    if (idx === -1) return;

    currentPage = Math.floor(idx / ROWS_PER_PAGE) + 1;
    showPage();
    loadMetrics();

    var row = document.querySelector('#inventoryTable tbody tr[data-id="' + id + '"]');
    if (row) {
        row.scrollIntoView({ behavior: 'smooth', block: 'center' });
        row.classList.add('row-highlight');
        setTimeout(function () { row.classList.remove('row-highlight'); }, 1500);
    }

    toggleLowStockPreview(false);
}

document.getElementById('lowStockCard').addEventListener('click', function () {
    toggleLowStockPreview();
});

document.getElementById('closeLowStockPreview').addEventListener('click', function (e) {
    e.stopPropagation();
    toggleLowStockPreview(false);
});

document.getElementById('lowStockPreviewList').addEventListener('click', function (e) {
    var item = e.target.closest('[data-jump-id]');
    if (!item) return;
    jumpToProductRow(item.dataset.jumpId);
});

/* ─── SEARCH / FILTER (zone + category_id + text) ─────────── */
function computeMatches() {
    var searchVal   = document.getElementById('searchInput').value.toLowerCase();
    var zoneVal     = document.getElementById('zoneFilter').value;
    var categoryVal = document.getElementById('categoryFilter').value;

    document.querySelectorAll('#inventoryTable tbody tr').forEach(function (r) {
        var text   = r.textContent.toLowerCase();
        var zone   = r.dataset.zone || '';
        var catId  = r.dataset.categoryId || '';

        var matchSearch   = text.includes(searchVal);
        var matchZone     = zoneVal === 'all' || zone === zoneVal;
        var matchCategory = categoryVal === 'all' || catId === categoryVal;

        r.dataset.match = (matchSearch && matchZone && matchCategory) ? '1' : '0';
    });
}

/* ─── PAGINATION RENDER ────────────────────────────────────── */
function showPage() {
    var allRows = document.querySelectorAll('#inventoryTable tbody tr');
    var matched = [];

    allRows.forEach(function (r) {
        if (r.dataset.match === '0') { r.style.display = 'none'; }
        else { matched.push(r); }
    });

    var totalPages = Math.max(1, Math.ceil(matched.length / ROWS_PER_PAGE));
    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    var start = (currentPage - 1) * ROWS_PER_PAGE;
    var end   = start + ROWS_PER_PAGE;

    matched.forEach(function (r, idx) {
        r.style.display = (idx >= start && idx < end) ? '' : 'none';
    });

    renderPagination(matched.length, totalPages);
}

function renderPagination(matchedCount, totalPages) {
    var container = document.getElementById('paginationControls');
    if (!container) return;
    container.innerHTML = '';

    if (matchedCount === 0) { container.style.display = 'none'; return; }
    container.style.display = 'flex';

    var start = ((currentPage - 1) * ROWS_PER_PAGE) + 1;
    var end   = Math.min(currentPage * ROWS_PER_PAGE, matchedCount);

    var info = document.createElement('span');
    info.className   = 'pagination-info';
    info.textContent = 'Showing ' + start + '–' + end + ' of ' + matchedCount;
    container.appendChild(info);

    var nav = document.createElement('div');
    nav.className = 'pagination-nav';

    var prevBtn = document.createElement('button');
    prevBtn.type = 'button'; prevBtn.className = 'page-btn';
    prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
    prevBtn.disabled = currentPage === 1;
    prevBtn.addEventListener('click', function () { goToPage(currentPage - 1); });
    nav.appendChild(prevBtn);

    getPageNumbers(currentPage, totalPages, 5).forEach(function (p) {
        if (p === '...') {
            var span = document.createElement('span');
            span.className = 'page-ellipsis'; span.textContent = '…';
            nav.appendChild(span);
        } else {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'page-btn' + (p === currentPage ? ' active' : '');
            btn.textContent = p;
            btn.addEventListener('click', function () { goToPage(p); });
            nav.appendChild(btn);
        }
    });

    var nextBtn = document.createElement('button');
    nextBtn.type = 'button'; nextBtn.className = 'page-btn';
    nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
    nextBtn.disabled = currentPage === totalPages;
    nextBtn.addEventListener('click', function () { goToPage(currentPage + 1); });
    nav.appendChild(nextBtn);

    container.appendChild(nav);
}

function getPageNumbers(current, total, maxButtons) {
    if (total <= maxButtons) {
        var arr = [];
        for (var i = 1; i <= total; i++) arr.push(i);
        return arr;
    }
    var pages = [1];
    var left  = Math.max(2, current - 1);
    var right = Math.min(total - 1, current + 1);
    if (left > 2) pages.push('...');
    for (var j = left; j <= right; j++) pages.push(j);
    if (right < total - 1) pages.push('...');
    pages.push(total);
    return pages;
}

function goToPage(p) { currentPage = p; showPage(); }

function refreshTable() {
    computeMatches();
    showPage();
    loadMetrics();
}

refreshTable();

document.getElementById('searchInput').addEventListener('keyup', function () {
    currentPage = 1; refreshTable();
});
document.getElementById('zoneFilter').addEventListener('change', function () {
    currentPage = 1; refreshTable();
});
document.getElementById('categoryFilter').addEventListener('change', function () {
    currentPage = 1; refreshTable();
});

/* ─── IMPORT CSV ───────────────────────────────────────────── */
document.getElementById('openImportBtn').addEventListener('click', function () {
    document.getElementById('importFileInput').click();
});

document.getElementById('importFileInput').addEventListener('change', function (event) {
    var file = event.target.files[0];
    if (!file) return;

    showLoading('Importing CSV…');

    var formData = new FormData();
    formData.append('action', 'import_products');
    formData.append('import_file', file);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    fetch('/inventory/import', { method: 'POST', body: formData })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.status === 'success') {
                showToast(data.message);
                // Sadyang hindi tinatawag ang hideLoading() dito — mananatili
                // itong naka-loading hanggang mag-reload ang page, para smooth
                // ang transition papunta sa refreshed data.
                window.setTimeout(function () { window.location.reload(); }, 1000);
            } else {
                hideLoading();
                showToast(data.message, 'error');
            }
        })
        .catch(function () {
            hideLoading();
            showToast('Upload failed. Check your connection.', 'error');
        })
        .finally(function () { event.target.value = ''; });
});

/* ─── EXPORT CSV/XLSX (form submit, hindi fetch) ───────────── */
document.getElementById('exportForm').addEventListener('submit', function () {
    showLoading('Preparing export…');
    // Awtomatikong ito-trigger ang file download ng browser; safety net
    // na lang ang timeout na ito kung sakaling matagal o may error.
    setTimeout(hideLoading, 4000);
});

/* ─── MODAL OPEN / CLOSE ───────────────────────────────────── */
function openModal(id) { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }

['addProductModal', 'editProductModal', 'deleteConfirmModal', 'restockModal', 'addCategoryModal'].forEach(function (id) {
    document.getElementById(id).addEventListener('click', function (e){
        if (e.target === this) closeModal(id);
    });
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeModal('addProductModal');
        closeModal('editProductModal');
        closeModal('deleteConfirmModal');
        closeModal('restockModal');
        closeModal('addCategoryModal');
        toggleLowStockPreview(false);
    }
});

/* ─── ADD PRODUCT ──────────────────────────────────────────── */
document.getElementById('openAddBtn').addEventListener('click', function () {
    document.getElementById('addName').value     = '';
    document.getElementById('addCategory').value = '';
    document.getElementById('addStock').value    = '';
    document.getElementById('addPrice').value    = '';
    toggleStockField('addCategory', 'addStock', 'addStockNote');
    openModal('addProductModal');
    document.getElementById('addName').focus();
});

document.getElementById('closeAddBtn').addEventListener('click',  function () { closeModal('addProductModal'); });
document.getElementById('cancelAddBtn').addEventListener('click', function () { closeModal('addProductModal'); });

document.getElementById('submitAddBtn').addEventListener('click', function () {
    var name       = document.getElementById('addName').value.trim();
    var catSelect  = document.getElementById('addCategory');
    var categoryId = catSelect.value;
    var opt        = catSelect.options[catSelect.selectedIndex];
    var isTracked  = opt && opt.dataset.tracked === '1';
    var stock      = document.getElementById('addStock').value.trim();
    var price      = document.getElementById('addPrice').value.trim() || '0';
    var stockVal   = isTracked ? stock : null;

    if (!name || !categoryId || (isTracked && !stock)) {
        showToast('Please fill in all required fields!', 'error');
        return;
    }

    post('/inventory/add', { action: 'add_product', name: name, category_id: categoryId, stock: stockVal, price: price },
        function (data) {
            if (data.status === 'success') {
                showToast(data.message);

                var tbody          = document.querySelector('#inventoryTable tbody');
                var tr              = document.createElement('tr');
                var priceFormatted  = parseFloat(price).toFixed(2);
                var cat             = data.category;

                tr.dataset.id         = data.id;
                tr.dataset.name       = name;
                tr.dataset.categoryId = cat.id;
                tr.dataset.cat        = cat.name;
                tr.dataset.zone       = cat.zone;
                tr.dataset.tracked    = cat.is_stock_tracked ? '1' : '0';
                tr.dataset.stock      = stockVal === null ? '' : parseInt(stockVal);
                tr.dataset.price      = priceFormatted;
                tr.dataset.threshold  = cat.low_stock_threshold || 20;

                tr.innerHTML =
                    '<td>' + escHtml(name) + '</td>' +
                    '<td>' + escHtml(cat.name) + ' <span class="zone-tag">' + escHtml(cat.zone) + '</span></td>' +
                    '<td>' + (stockVal === null ? '—' : parseInt(stockVal)) + '</td>' +
                    '<td>₱' + priceFormatted + '</td>' +
                    '<td>' + (stockVal === null
                        ? '<span class="status-badge status-good">N/A</span>'
                        : statusBadge(stockVal, tr.dataset.threshold)) + '</td>' +
                    '<td>' +
                        (stockVal !== null
                            ? '<button class="action-btn restock-btn" data-action="restock"><i class="fas fa-plus-circle"></i> Restock</button>'
                            : '') +
                        '<button class="action-btn edit-btn"   data-action="edit">Edit</button>' +
                        '<button class="action-btn delete-btn" data-action="delete">Delete</button>' +
                    '</td>';

                tbody.appendChild(tr);
                refreshTable();
                closeModal('addProductModal');
            } else {
                showToast(data.message, 'error');
            }
        },
        'Adding product…'
    );
});

/* ─── EDIT PRODUCT ─────────────────────────────────────────── */
document.getElementById('closeEditBtn').addEventListener('click',  function () { closeModal('editProductModal'); });
document.getElementById('cancelEditBtn').addEventListener('click', function () { closeModal('editProductModal'); });

document.getElementById('submitEditBtn').addEventListener('click', function () {
    var id         = document.getElementById('editProductId').value;
    var name       = document.getElementById('editProductName').value.trim();
    var catSelect  = document.getElementById('editProductCategory');
    var categoryId = catSelect.value;
    var opt        = catSelect.options[catSelect.selectedIndex];
    var isTracked  = opt && opt.dataset.tracked === '1';
    var stock      = document.getElementById('editProductStock').value.trim();
    var price      = document.getElementById('editProductPrice').value.trim() || '0';
    var stockVal   = isTracked ? stock : null;

    if (!name || !categoryId || (isTracked && !stock)) {
        showToast('Please fill in all required fields!', 'error');
        return;
    }

    post('/inventory/update', { action: 'edit_product', id: id, name: name, category_id: categoryId, stock: stockVal === null ? 0 : stockVal, price: price },
        function (data) {
            if (data.status === 'success') {
                showToast(data.message);

                var row = document.querySelector('#inventoryTable tbody tr[data-id="' + id + '"]');
                if (row) {
                    var priceFormatted = parseFloat(price).toFixed(2);
                    var cat = data.category;

                    row.dataset.name       = name;
                    row.dataset.categoryId = cat.id;
                    row.dataset.cat        = cat.name;
                    row.dataset.zone       = cat.zone;
                    row.dataset.tracked    = cat.is_stock_tracked ? '1' : '0';
                    row.dataset.stock      = stockVal === null ? '' : parseInt(stockVal);
                    row.dataset.price      = priceFormatted;
                    if (cat.low_stock_threshold) row.dataset.threshold = cat.low_stock_threshold;

                    row.children[0].textContent = name;
                    row.children[1].innerHTML   = escHtml(cat.name) + ' <span class="zone-tag">' + escHtml(cat.zone) + '</span>';
                    row.children[2].textContent = stockVal === null ? '—' : parseInt(stockVal);
                    row.children[3].textContent = '₱' + priceFormatted;
                    row.children[4].innerHTML   = stockVal === null
                        ? '<span class="status-badge status-good">N/A</span>'
                        : statusBadge(stockVal, row.dataset.threshold);
                }
                refreshTable();
                closeModal('editProductModal');
            } else {
                showToast(data.message, 'error');
            }
        },
        'Saving changes…'
    );
});

/* ─── DELETE PRODUCT ───────────────────────────────────────── */
var pendingDeleteId = null;

document.getElementById('closeDeleteBtn').addEventListener('click',  function () { closeModal('deleteConfirmModal'); });
document.getElementById('cancelDeleteBtn').addEventListener('click', function () { closeModal('deleteConfirmModal'); });

document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
    if (!pendingDeleteId) return;
    var delId = pendingDeleteId;

    post('/inventory/delete', { action: 'delete_product', id: delId },
        function (data) {
            if (data.status === 'success') {
                showToast(data.message);
                var row = document.querySelector('#inventoryTable tbody tr[data-id="' + delId + '"]');
                if (row) row.remove();
                refreshTable();
                closeModal('deleteConfirmModal');
                pendingDeleteId = null;
            } else {
                showToast(data.message, 'error');
            }
        },
        'Deleting product…'
    );
});

/* ─── RESTOCK PRODUCT ──────────────────────────────────────── */
document.getElementById('closeRestockBtn').addEventListener('click',  function () { closeModal('restockModal'); });
document.getElementById('cancelRestockBtn').addEventListener('click', function () { closeModal('restockModal'); });

document.getElementById('submitRestockBtn').addEventListener('click', function () {
    var id  = document.getElementById('restockProductId').value;
    var qty = document.getElementById('restockQty').value.trim();

    if (!qty || parseInt(qty) <= 0) {
        showToast('Please enter a valid quantity!', 'error');
        return;
    }

    post('/inventory/restock', { action: 'restock_product', id: id, qty: qty }, function (data) {
        if (data.status === 'success') {
            showToast(data.message);
            var row = document.querySelector('#inventoryTable tbody tr[data-id="' + id + '"]');
            if (row) {
                var newStock = data.new_stock;
                row.dataset.stock           = newStock;
                row.children[2].textContent = newStock;
                row.children[4].innerHTML   = statusBadge(newStock, row.dataset.threshold);
            }
            loadMetrics();
            closeModal('restockModal');
        } else {
            showToast(data.message, 'error');
        }
    }, 'Updating stock…');
});

/* ─── TABLE CLICK DELEGATION ───────────────────────────────── */
document.getElementById('inventoryTable').addEventListener('click', function (e) {
    var btn = e.target.closest('[data-action]');
    if (!btn) return;

    var row    = btn.closest('tr');
    var id     = row.dataset.id;
    var name   = row.dataset.name;
    var catId  = row.dataset.categoryId;
    var stock  = row.dataset.stock;
    var price  = row.dataset.price;
    var action = btn.dataset.action;

    if (action === 'edit') {
        document.getElementById('editProductId').value       = id;
        document.getElementById('editProductName').value     = name;
        document.getElementById('editProductCategory').value = catId;
        document.getElementById('editProductStock').value    = stock;
        document.getElementById('editProductPrice').value    = price;
        toggleStockField('editProductCategory', 'editProductStock', 'editStockNote');
        openModal('editProductModal');
        document.getElementById('editProductName').focus();
    }

    if (action === 'delete') {
        pendingDeleteId = id;
        document.getElementById('deleteProductName').textContent = name;
        openModal('deleteConfirmModal');
    }

    if (action === 'restock') {
        document.getElementById('restockProductId').value          = id;
        document.getElementById('restockProductName').textContent  = name;
        document.getElementById('restockCurrentStock').textContent = stock;
        document.getElementById('restockQty').value                = '';
        openModal('restockModal');
        document.getElementById('restockQty').focus();
    }
});

/* ─── ESCAPE HELPER ────────────────────────────────────────── */
function escHtml(str) {
    var d = document.createElement('div');
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
}

/* ─── TOGGLE STOCK FIELD ───────────────────────────────────── */
function toggleStockField(selectId, inputId, noteId) {
    var select = document.getElementById(selectId);
    var input  = document.getElementById(inputId);
    var note   = document.getElementById(noteId);
    var opt    = select.options[select.selectedIndex];
    var isTracked = opt && opt.dataset.tracked === '1';

    if (!isTracked) {
        input.value        = '';
        input.disabled     = true;
        input.placeholder  = 'Not tracked';
        note.style.display = 'inline';
    } else {
        input.disabled     = false;
        input.placeholder  = 'e.g. 100';
        note.style.display = 'none';
    }
}

document.getElementById('addCategory').addEventListener('change', function () {
    toggleStockField('addCategory', 'addStock', 'addStockNote');
});
document.getElementById('editProductCategory').addEventListener('change', function () {
    toggleStockField('editProductCategory', 'editProductStock', 'editStockNote');
});

/* ─── ADD NEW CATEGORY ─────────────────────────────────────── */
var addCatOpenedFrom = null;

function openAddCategoryModal(from) {
    addCatOpenedFrom = from;
    document.getElementById('newCatName').value    = '';
    document.getElementById('newCatZone').value     = '';
    document.getElementById('newCatType').value     = 'merchandise';
    document.getElementById('newCatTracked').checked = true;
    document.getElementById('addCatError').textContent = '';
    openModal('addCategoryModal');
    document.getElementById('newCatName').focus();
}

document.getElementById('openAddCatFromAdd').addEventListener('click', function () { openAddCategoryModal('add'); });
document.getElementById('openAddCatFromEdit').addEventListener('click', function () { openAddCategoryModal('edit'); });
document.getElementById('closeAddCatBtn').addEventListener('click', function () { closeModal('addCategoryModal'); });
document.getElementById('cancelAddCatBtn').addEventListener('click', function () { closeModal('addCategoryModal'); });

document.getElementById('submitAddCatBtn').addEventListener('click', function () {
    var name    = document.getElementById('newCatName').value.trim();
    var zone    = document.getElementById('newCatZone').value.trim();
    var type    = document.getElementById('newCatType').value;
    var tracked = document.getElementById('newCatTracked').checked;

    if (!name || !zone) {
        document.getElementById('addCatError').textContent = 'Category name and zone are required.';
        return;
    }

    post('/inventory/add-category', {
        action: 'add_category',
        category_name: name,
        zone: zone,
        category_type: type,
        is_stock_tracked: tracked ? 1 : 0
    }, function (data) {
        if (data.status === 'success') {
            showToast(data.message);
            var cat = data.category;

            [document.getElementById('addCategory'), document.getElementById('editProductCategory')].forEach(function (select) {
                var groupLabel = cat.zone;
                var group = Array.from(select.querySelectorAll('optgroup')).find(function (g) { return g.label === groupLabel; });
                if (!group) {
                    group = document.createElement('optgroup');
                    group.label = groupLabel;
                    select.appendChild(group);
                }
                var option = document.createElement('option');
                option.value = cat.category_id;
                option.textContent = cat.category_name;
                option.dataset.tracked = cat.is_stock_tracked ? '1' : '0';
                option.dataset.zone = cat.zone;
                group.appendChild(option);
            });

            var zoneFilter = document.getElementById('zoneFilter');
            var zoneExists = Array.from(zoneFilter.options).some(function (o) { return o.value === cat.zone; });
            if (!zoneExists) {
                var zOpt = document.createElement('option');
                zOpt.value = cat.zone; zOpt.textContent = cat.zone;
                zoneFilter.appendChild(zOpt);
            }

            var catFilter = document.getElementById('categoryFilter');
            var fGroup = Array.from(catFilter.querySelectorAll('optgroup')).find(function (g) { return g.label === cat.zone; });
            if (!fGroup) {
                fGroup = document.createElement('optgroup');
                fGroup.label = cat.zone;
                catFilter.appendChild(fGroup);
            }
            var fOpt = document.createElement('option');
            fOpt.value = cat.category_id; fOpt.textContent = cat.category_name; fOpt.dataset.zone = cat.zone;
            fGroup.appendChild(fOpt);

            if (addCatOpenedFrom === 'add') {
                document.getElementById('addCategory').value = cat.category_id;
                toggleStockField('addCategory', 'addStock', 'addStockNote');
            } else if (addCatOpenedFrom === 'edit') {
                document.getElementById('editProductCategory').value = cat.category_id;
                toggleStockField('editProductCategory', 'editProductStock', 'editStockNote');
            }

            closeModal('addCategoryModal');
        } else {
            document.getElementById('addCatError').textContent = data.message;
        }
    }, 'Saving category…');
});