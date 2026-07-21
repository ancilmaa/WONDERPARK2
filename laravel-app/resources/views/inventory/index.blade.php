@extends('layouts.sidebar')

@section('title', 'Inventory')

@push('head')
<link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,600;8..60,700&display=swap" rel="stylesheet">
@endpush

@section('styles')
<style>
  .serif{font-family:'Source Serif 4', serif;}

  /* ── TOOLBAR (replaces old page-header) ─────────────────── */
  .toolbar{background:var(--card);padding:22px 26px;margin-bottom:20px;border-radius:16px;box-shadow:var(--shadow-sm);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:18px;}
  .toolbar .eyebrow{font-size:.68rem;font-weight:700;color:var(--pink-deep);text-transform:uppercase;letter-spacing:.09em;margin-bottom:6px;}
  .toolbar h2{font-family:'Source Serif 4', serif;font-size:1.4rem;font-weight:700;color:var(--ink);}
  .toolbar-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
  .toolbar-actions input[type="text"]{padding:9px 12px;border:1px solid var(--line-strong);border-radius:10px;font-size:12.5px;font-family:'Inter',sans-serif;color:var(--ink-soft);background:var(--bg);min-width:180px;max-width:220px;}
  .toolbar-actions select{padding:9px 12px;border:1px solid var(--line-strong);border-radius:10px;font-size:12.5px;font-family:'Inter',sans-serif;color:var(--ink-soft);background:var(--bg);cursor:pointer;}
  .toolbar-actions input[type="text"]:focus, .toolbar-actions select:focus{outline:none;border-color:var(--pink);box-shadow:0 0 0 3px var(--pink-light);}

  #uploadBtn{padding:11px 18px!important;background:#fff!important;color:var(--ink)!important;border:1px solid var(--line-strong)!important;border-radius:10px!important;cursor:pointer;font-weight:600;font-size:13px;font-family:'Inter',sans-serif;white-space:nowrap;transition:background .15s ease, border-color .15s ease;}
  #uploadBtn:hover{background:var(--bg)!important;border-color:var(--pink)!important;}
  #openImportBtn{padding:11px 18px;background:#fff;color:var(--ink);border:1px solid var(--line-strong);border-radius:10px;cursor:pointer;font-weight:600;font-size:13px;font-family:'Inter',sans-serif;white-space:nowrap;transition:background .15s ease, border-color .15s ease;}
  #openImportBtn:hover{background:var(--bg);border-color:var(--pink);}
  .print-btn{padding:11px 20px;background:var(--pink-deep);color:#fff;border:none;border-radius:10px;cursor:pointer;font-size:13px;font-weight:600;font-family:'Inter',sans-serif;white-space:nowrap;transition:background .15s ease, box-shadow .15s ease;}
  .print-btn:hover{background:var(--pink-dark);box-shadow:0 0 0 3px var(--pink-light);}

  /* ── STATS ROW (replaces old metrics-grid) ──────────────── */
  .stats-row{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px;}
  .stat-card{background:var(--card);border-radius:16px;padding:18px 20px;box-shadow:var(--shadow-sm);transition:.15s;}
  .stat-card .label{font-size:.68rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:9px;}
  .stat-card .value{font-family:'Source Serif 4', serif;font-size:1.7rem;font-weight:700;color:var(--ink);line-height:1;}
  .stat-card .sub{font-size:11px;color:var(--muted);margin-top:7px;}
  .stat-card.low{cursor:pointer;user-select:none;}
  .stat-card.low:hover{box-shadow:var(--shadow-md);transform:translateY(-2px);}
  .stat-card.low.open{box-shadow:var(--shadow-md);}
  .stat-card.low .value{color:var(--amber);}
  .stat-card.low .sub{color:var(--amber);}
  .stat-card.low .sub i{margin-left:4px;transition:transform .15s;}
  .stat-card.low.open .sub i{transform:rotate(180deg);}

  /* ── LOW STOCK PREVIEW PANEL ─────────────────────────────── */
  .low-stock-preview{
    display:none;background:var(--card);border:1px solid var(--amber);border-left:4px solid var(--amber);
    border-radius:14px;padding:14px 18px;margin:-10px 0 20px;box-shadow:var(--shadow-sm);
    animation:slideDown .18s ease;
  }
  .low-stock-preview.active{display:block;}
  @keyframes slideDown{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}
  .low-stock-preview-header{
    display:flex;justify-content:space-between;align-items:center;font-weight:700;
    color:var(--amber);font-size:.85rem;margin-bottom:10px;padding-bottom:8px;border-bottom:1px solid var(--line);
  }
  .low-stock-preview-header .close-btn{font-size:20px;}
  .low-stock-preview-list{display:flex;flex-direction:column;gap:6px;max-height:240px;overflow-y:auto;}
  .low-stock-preview-item{
    display:flex;justify-content:space-between;align-items:center;padding:9px 12px;
    background:var(--pink-pale);border-radius:9px;font-size:.83rem;cursor:pointer;transition:.15s;
  }
  .low-stock-preview-item:hover{background:var(--pink-light);}
  .low-stock-preview-item .lsp-name{font-weight:600;color:var(--ink);display:flex;flex-direction:column;}
  .low-stock-preview-item .lsp-cat{font-weight:400;color:var(--muted);font-size:.72rem;}
  .low-stock-preview-item .lsp-qty{font-weight:700;color:#ef4444;font-size:.78rem;white-space:nowrap;margin-left:10px;}
  .low-stock-preview-empty{color:var(--muted);font-size:.82rem;text-align:center;padding:12px;}

  .report-section{background:var(--card);padding:20px;border-radius:16px;margin-bottom:20px;box-shadow:var(--shadow-sm);border:1px solid var(--line);height:500px;overflow:hidden;display:flex;flex-direction:column;}
  .report-title{font-family:'Source Serif 4', serif;font-size:1.1rem;font-weight:700;margin-bottom:14px;padding-bottom:12px;border-bottom:2px solid var(--pink-light);color:var(--ink);display:flex;align-items:center;gap:8px;}
  .table-wrapper{overflow-y:auto;flex:1;border:1px solid var(--line);border-radius:12px;}
  .pagination-bar{display:flex;align-items:center;justify-content:space-between;padding-top:14px;gap:10px;flex-wrap:wrap;}
  .pagination-info{font-size:.78rem;color:var(--muted);font-weight:600;}
  .pagination-nav{display:flex;align-items:center;gap:4px;}
  .page-btn{min-width:32px;height:32px;padding:0 8px;border:1px solid var(--line);background:var(--card);border-radius:8px;cursor:pointer;font-size:.8rem;font-weight:700;color:var(--ink-soft);transition:.15s;}
  .page-btn:hover:not(:disabled){border-color:var(--pink);color:var(--pink-dark);}
  .page-btn.active{background:var(--pink-deep);color:#fff;border-color:transparent;}
  .page-btn:disabled{opacity:.4;cursor:not-allowed;}
  .page-ellipsis{padding:0 4px;color:var(--muted);font-size:.8rem;}
  .report-table{width:100%;border-collapse:collapse;}
  .report-table thead{position:sticky;top:0;z-index:5;}
  .report-table thead th{background:var(--ink);color:#fff;padding:12px 14px;text-align:left;font-weight:600;font-size:.78rem;text-transform:uppercase;letter-spacing:.03em;border-bottom:none;}
  .report-table td{padding:12px 14px;border-bottom:1px solid var(--line);font-size:.87rem;color:var(--ink-soft);}
  .report-table tr:hover{background:var(--pink-pale);}
  .report-table tr.row-highlight{background:var(--pink-light)!important;animation:pulseHighlight 1.2s ease;}
  @keyframes pulseHighlight{0%,100%{background:var(--pink-light);}50%{background:var(--pink);}}
  .zone-tag{display:inline-block;font-size:.68rem;font-weight:700;padding:2px 8px;border-radius:999px;background:var(--pink-light);color:var(--pink-dark);margin-left:6px;}
  .status-badge{padding:5px 12px;border-radius:999px;color:#fff;font-size:.72rem;font-weight:700;}
  .status-good{background:var(--green);}
  .status-low{background:#ef4444;}
  .action-btn{border:none;padding:7px 12px;border-radius:8px;color:#fff;cursor:pointer;margin-right:5px;font-size:.76rem;font-weight:600;transition:.15s;}
  .action-btn:hover{filter:brightness(1.08);transform:translateY(-1px);}
  .edit-btn{background:#7C5CE0;}
  .delete-btn{background:#ef4444;}
  .restock-btn{background:var(--amber)!important;}

  /* ── TOAST (attendance-style toast stack) ────────────────── */
  #toastStack{position:fixed;top:22px;left:50%;transform:translateX(-50%);z-index:9999;display:flex;flex-direction:column;align-items:center;gap:10px;width:100%;max-width:420px;padding:0 16px;pointer-events:none;}
  .toast{pointer-events:auto;width:100%;display:flex;align-items:flex-start;gap:12px;background:#fff;color:var(--ink);padding:14px 16px;border-radius:14px;border-left:4px solid var(--ink);box-shadow:var(--shadow-md);font-size:13px;font-weight:500;line-height:1.4;opacity:0;transform:translateY(-16px) scale(.97);animation:toastIn .35s cubic-bezier(.34,1.56,.64,1) forwards;position:relative;overflow:hidden;}
  .toast.hide{animation:toastOut .28s ease forwards;}
  .toast .toast-icon{flex-shrink:0;width:26px;height:26px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:13px;color:#fff;background:var(--ink-soft);}
  .toast.toast-success{border-left-color:var(--present);}
  .toast.toast-success .toast-icon{background:var(--present);}
  .toast.toast-error, .toast.error{border-left-color:#ef4444;}
  .toast.toast-error .toast-icon{background:#ef4444;}
  .toast.toast-info .toast-icon{background:var(--pink-deep);}
  .toast .toast-body{flex:1;min-width:0;padding-top:2px;}
  .toast .toast-title{font-weight:700;font-size:12.5px;margin-bottom:2px;color:var(--ink);}
  .toast .toast-msg{color:var(--ink-soft);font-size:12.5px;word-break:break-word;}
  .toast .toast-close{flex-shrink:0;background:none;border:none;cursor:pointer;color:var(--muted);font-size:13px;padding:2px;line-height:1;margin-top:1px;}
  .toast .toast-close:hover{color:var(--ink);}
  .toast .toast-bar{position:absolute;left:0;bottom:0;height:3px;border-radius:0 0 0 14px;background:currentColor;opacity:.35;animation:toastShrink 3s linear forwards;}
  @keyframes toastIn{to{opacity:1;transform:translateY(0) scale(1);}}
  @keyframes toastOut{to{opacity:0;transform:translateY(-12px) scale(.96);}}
  @keyframes toastShrink{from{width:100%;}to{width:0%;}}

  .modal{display:none;position:fixed;z-index:2000;inset:0;background:rgba(26,21,35,.55);align-items:center;justify-content:center;}
  .modal.active{display:flex;}
  .modal-content{background:var(--card);padding:26px;border-radius:18px;box-shadow:var(--shadow-md);width:90%;max-width:460px;animation:slideIn .25s ease;}
  @keyframes slideIn{from{transform:translateY(-30px);opacity:0}to{transform:translateY(0);opacity:1}}
  .modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;border-bottom:2px solid var(--pink-light);padding-bottom:14px;}
  .modal-header h2{margin:0;color:var(--ink);font-size:1.05rem;font-weight:700;display:flex;align-items:center;gap:8px;font-family:'Source Serif 4', serif;}
  .close-btn{background:none;border:none;font-size:24px;cursor:pointer;color:var(--muted);line-height:1;}
  .close-btn:hover{color:var(--ink);}
  .modal-body{margin-bottom:18px;}
  .form-group{margin-bottom:14px;}
  .form-group label{display:block;margin-bottom:6px;font-weight:600;color:var(--ink);font-size:.82rem;}
  .form-group input, .form-group select{width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:9px;font-size:.85rem;font-family:'Inter',sans-serif;box-sizing:border-box;color:var(--ink);}
  .form-group input:focus, .form-group select:focus{outline:none;border-color:var(--pink);box-shadow:0 0 0 3px var(--pink-light);}
  .form-row{display:flex;gap:10px;}
  .form-row .form-group{flex:1;}
  .checkbox-row{display:flex;align-items:center;gap:8px;margin-bottom:14px;}
  .checkbox-row input{width:auto;}
  .checkbox-row label{margin:0;}
  .modal-footer{display:flex;gap:10px;justify-content:flex-end;}
  .modal-footer button{padding:10px 20px;border:none;border-radius:9px;cursor:pointer;font-weight:700;font-size:.83rem;transition:.15s;}
  .btn-submit{background:var(--pink-deep);color:#fff;}
  .btn-submit:hover{filter:brightness(1.05);transform:translateY(-1px);}
  .btn-cancel{background:var(--pink-pale);color:var(--ink-soft);}
  .btn-cancel:hover{background:var(--pink-light);}
  .btn-danger{background:#ef4444;color:#fff;}
  .btn-danger:hover{background:#dc2626;}
  .add-cat-link{display:inline-block;margin-top:6px;font-size:.76rem;font-weight:700;color:var(--pink-dark);cursor:pointer;}
  .add-cat-link:hover{text-decoration:underline;}

  .loading-overlay{
    position:fixed;inset:0;background:rgba(243,239,243,.85);backdrop-filter:blur(2px);
    z-index:5000;display:flex;flex-direction:column;align-items:center;justify-content:center;
    gap:14px;opacity:0;visibility:hidden;transition:opacity .2s ease;
  }
  .loading-overlay.active{opacity:1;visibility:visible;}
  .loading-spinner{
    width:44px;height:44px;border-radius:50%;
    border:4px solid var(--pink-light);border-top-color:var(--pink);
    animation:spin .7s linear infinite;
  }
  .loading-text{font-size:.85rem;font-weight:700;color:var(--pink-dark);letter-spacing:.02em;}
  @keyframes spin{to{transform:rotate(360deg);}}

  @media(max-width:900px){
    .stats-row{grid-template-columns:1fr 1fr;}
    .toolbar h2{font-size:1.05rem;}
    .toolbar-actions{width:100%;}
    .toolbar-actions input[type="text"]{max-width:none;width:100%;}
    .toolbar-actions select{width:100%;}
  }
  @media(max-width:520px){
    .stats-row{grid-template-columns:1fr;}
  }
</style>
@endsection

@section('content')

  <div class="toolbar no-print">
    <div>
      <div class="eyebrow">Lipa Branch &middot; Inventory</div>
      <h2>Inventory Management</h2>
    </div>

    <div class="toolbar-actions">
      <input type="text" id="searchInput" placeholder="Search products…">

      <select id="zoneFilter">
        <option value="all">All Zones</option>
        @foreach($zones as $zone)
          <option value="{{ $zone }}">{{ $zone }}</option>
        @endforeach
      </select>

      <select id="categoryFilter">
        <option value="all">All Categories</option>
        @foreach($categoriesByZone as $zone => $cats)
          <optgroup label="{{ $zone }}">
            @foreach($cats as $cat)
              <option value="{{ $cat->category_id }}" data-zone="{{ $cat->zone }}">{{ $cat->category_name }}</option>
            @endforeach
          </optgroup>
        @endforeach
      </select>

      <form id="exportForm" method="post" action="{{ route('inventory.export') }}" style="margin:0;">
        @csrf
        <input type="hidden" name="action" value="export_products">
        <button type="submit" id="uploadBtn"><i class="fas fa-file-export"></i> Export</button>
      </form>

      <input type="file" id="importFileInput" accept=".csv" style="display:none">
      <button id="openImportBtn" type="button"><i class="fas fa-file-import"></i> Import CSV</button>

      <button id="openAddBtn" type="button" class="print-btn"><i class="fas fa-plus"></i> Add Product</button>
    </div>
  </div>

  <div class="stats-row no-print">
    <div class="stat-card">
      <div class="label">Total Products</div>
      <div class="value" id="totalProducts">0</div>
      <div class="sub">Active items</div>
    </div>
    <div class="stat-card low" id="lowStockCard" title="Click to preview low stock items">
      <div class="label">Low Stock Items</div>
      <div class="value" id="lowStocks">0</div>
      <div class="sub">Need restocking <i class="fas fa-chevron-down"></i></div>
    </div>
    <div class="stat-card">
      <div class="label">Total Stock</div>
      <div class="value" id="totalStocks">0</div>
      <div class="sub">Units available</div>
    </div>
  </div>

  <!-- ══ LOW STOCK PREVIEW PANEL ════════════════════════════ -->
  <div class="low-stock-preview" id="lowStockPreview">
    <div class="low-stock-preview-header">
      <span><i class="fas fa-triangle-exclamation"></i> Low Stock Items — click one to jump to it</span>
      <button type="button" class="close-btn" id="closeLowStockPreview">&times;</button>
    </div>
    <div class="low-stock-preview-list" id="lowStockPreviewList"></div>
  </div>

  <!-- ══ ADD MODAL ══════════════════════════════════════════ -->
  <div id="addProductModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2><i class="fas fa-plus-circle" style="color:var(--pink)"></i> Add New Product</h2>
        <button class="close-btn" id="closeAddBtn">&times;</button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Product Name <span style="color:#ef4444">*</span></label>
          <input type="text" id="addName" placeholder="e.g. Demi Apple">
        </div>
        <div class="form-group">
          <label>Category <span style="color:#ef4444">*</span></label>
          <select id="addCategory">
            <option value="">Select Category</option>
            @foreach($categoriesByZone as $zone => $cats)
              <optgroup label="{{ $zone }}">
                @foreach($cats as $cat)
                  <option value="{{ $cat->category_id }}" data-tracked="{{ $cat->is_stock_tracked }}" data-zone="{{ $cat->zone }}">{{ $cat->category_name }}</option>
                @endforeach
              </optgroup>
            @endforeach
          </select>
          <span class="add-cat-link" id="openAddCatFromAdd">+ Add new category</span>
        </div>
        <div class="form-group">
          <label>Stock Quantity <span style="color:#ef4444">*</span></label>
          <input type="number" id="addStock" placeholder="e.g. 100" min="0">
          <small id="addStockNote" style="color:var(--muted);display:none">⚠️ This category does not track stock.</small>
        </div>
        <div class="form-group">
          <label>Price <span style="color:var(--muted);font-weight:400">(optional)</span></label>
          <input type="number" id="addPrice" placeholder="e.g. 85.00" step="0.01" min="0">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-cancel" id="cancelAddBtn">Cancel</button>
        <button type="button" class="btn-submit" id="submitAddBtn">Add Product</button>
      </div>
    </div>
  </div>

  <!-- ══ EDIT MODAL ═════════════════════════════════════════ -->
  <div id="editProductModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2><i class="fas fa-edit" style="color:#7C5CE0"></i> Edit Product</h2>
        <button class="close-btn" id="closeEditBtn">&times;</button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="editProductId">
        <div class="form-group">
          <label>Product Name</label>
          <input type="text" id="editProductName">
        </div>
        <div class="form-group">
          <label>Category</label>
          <select id="editProductCategory">
            <option value="">Select Category</option>
            @foreach($categoriesByZone as $zone => $cats)
              <optgroup label="{{ $zone }}">
                @foreach($cats as $cat)
                  <option value="{{ $cat->category_id }}" data-tracked="{{ $cat->is_stock_tracked }}" data-zone="{{ $cat->zone }}">{{ $cat->category_name }}</option>
                @endforeach
              </optgroup>
            @endforeach
          </select>
          <span class="add-cat-link" id="openAddCatFromEdit">+ Add new category</span>
        </div>
        <div class="form-group" id="editStockGroup">
          <label>Stock Quantity</label>
          <input type="number" id="editProductStock" placeholder="e.g. 100" min="0">
          <small id="editStockNote" style="color:var(--muted);display:none">⚠️ This category does not track stock.</small>
        </div>
        <div class="form-group">
          <label>Price</label>
          <input type="number" id="editProductPrice" step="0.01" min="0">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-cancel" id="cancelEditBtn">Cancel</button>
        <button type="button" class="btn-submit" id="submitEditBtn">Save Changes</button>
      </div>
    </div>
  </div>

  <!-- ══ ADD CATEGORY MODAL ═════════════════════════════════ -->
  <div id="addCategoryModal" class="modal">
    <div class="modal-content" style="max-width:400px">
      <div class="modal-header">
        <h2><i class="fas fa-tags" style="color:var(--pink)"></i> Add New Category</h2>
        <button class="close-btn" id="closeAddCatBtn">&times;</button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Category Name <span style="color:#ef4444">*</span></label>
          <input type="text" id="newCatName" placeholder="e.g. Souvenirs">
        </div>
        <div class="form-group">
          <label>Zone <span style="color:#ef4444">*</span></label>
          <input type="text" id="newCatZone" list="zoneDatalist" placeholder="e.g. Dino Adventure">
          <datalist id="zoneDatalist">
            @foreach($zones as $zone)
              <option value="{{ $zone }}">
            @endforeach
          </datalist>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Type <span style="color:#ef4444">*</span></label>
            <select id="newCatType">
              <option value="merchandise">Merchandise</option>
              <option value="ride">Ride / Pass</option>
              <option value="service">Service</option>
            </select>
          </div>
        </div>
        <div class="checkbox-row">
          <input type="checkbox" id="newCatTracked" checked>
          <label for="newCatTracked">Track stock for this category</label>
        </div>
      </div>
      <div id="addCatError" style="color:#ef4444;font-size:.8rem;margin-bottom:8px"></div>
      <div class="modal-footer">
        <button type="button" class="btn-cancel" id="cancelAddCatBtn">Cancel</button>
        <button type="button" class="btn-submit" id="submitAddCatBtn">Save Category</button>
      </div>
    </div>
  </div>

  <!-- ══ DELETE CONFIRM MODAL ═══════════════════════════════ -->
  <div id="deleteConfirmModal" class="modal">
    <div class="modal-content" style="max-width:380px">
      <div class="modal-header">
        <h2>🗑️ Confirm Delete</h2>
        <button class="close-btn" id="closeDeleteBtn">&times;</button>
      </div>
      <div class="modal-body" style="text-align:center">
        <p style="font-size:.9rem;color:var(--ink-soft);margin-bottom:6px">
          Are you sure you want to delete<br>
          <strong id="deleteProductName"></strong>?
        </p>
        <p style="color:#ef4444;font-size:.8rem">This cannot be undone.</p>
      </div>
      <div class="modal-footer" style="justify-content:center">
        <button type="button" class="btn-cancel" id="cancelDeleteBtn">Cancel</button>
        <button type="button" class="btn-danger" id="confirmDeleteBtn">Yes, Delete</button>
      </div>
    </div>
  </div>

  <!-- Inventory Table -->
  <div class="report-section">
    <div class="report-title">📦 Inventory List</div>

    <div class="table-wrapper">
      <table class="report-table" id="inventoryTable">
        <thead>
          <tr>
            <th>Product</th>
            <th>Category</th>
            <th>Stock</th>
            <th>Price</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($products as $p)
            @php
              $isNoStock = !$p->is_stock_tracked;
              $threshold = intval($p->low_stock_threshold ?? 20);
              $stock     = $isNoStock ? null : intval($p->stock);
              $isLow     = !$isNoStock && $stock <= $threshold;
              $price     = number_format(floatval($p->price ?? 0), 2);
            @endphp
            <tr data-id="{{ $p->product_id }}"
                data-name="{{ $p->product_name }}"
                data-category-id="{{ $p->category_id }}"
                data-cat="{{ $p->category_name }}"
                data-zone="{{ $p->zone }}"
                data-tracked="{{ $p->is_stock_tracked }}"
                data-stock="{{ $stock ?? '' }}"
                data-price="{{ $price }}"
                data-threshold="{{ $threshold }}">
              <td>{{ $p->product_name }}</td>
              <td>{{ $p->category_name }} <span class="zone-tag">{{ $p->zone }}</span></td>
              <td>{{ $isNoStock ? '—' : $stock }}</td>
              <td>₱{{ $price }}</td>
              <td>
                <span class="status-badge {{ $isNoStock ? 'status-good' : ($isLow ? 'status-low' : 'status-good') }}">
                  {{ $isNoStock ? 'N/A' : ($isLow ? 'Low Stock' : 'In Stock') }}
                </span>
              </td>
              <td>
                @if(in_array(strtolower(session('role')), ['admin', 'tl', 'manager']))
                  @if(!$isNoStock)
                    <button class="action-btn restock-btn" data-action="restock"><i class="fas fa-plus-circle"></i> Restock</button>
                  @endif
                  <button class="action-btn edit-btn" data-action="edit">Edit</button>
                  <button class="action-btn delete-btn" data-action="delete">Delete</button>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="pagination-bar" id="paginationControls"></div>
  </div>

  <!-- ══ RESTOCK MODAL ══════════════════════════════════════ -->
  <div id="restockModal" class="modal">
    <div class="modal-content" style="max-width:380px">
      <div class="modal-header">
        <h2><i class="fas fa-plus-circle" style="color:var(--amber)"></i> Restock Product</h2>
        <button class="close-btn" id="closeRestockBtn">&times;</button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="restockProductId">
        <p style="margin-bottom:12px;color:var(--ink-soft);font-size:.87rem">
          Restocking: <strong id="restockProductName"></strong><br>
          <span style="font-size:.78rem;color:var(--muted)">Current stock: <strong id="restockCurrentStock"></strong></span>
        </p>
        <div class="form-group">
          <label>Quantity to Add <span style="color:#ef4444">*</span></label>
          <input type="number" id="restockQty" placeholder="e.g. 50" min="1">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-cancel" id="cancelRestockBtn">Cancel</button>
        <button type="button" class="btn-submit" id="submitRestockBtn" style="background:var(--amber)">Add Stock</button>
      </div>
    </div>
  </div>

  <div id="toastStack" aria-live="polite"></div>
  <div id="toast" class="toast" style="display:none;"></div>

  <!-- ══ LOADING OVERLAY ══════════════════════════════════════ -->
  <div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner"></div>
    <div class="loading-text" id="loadingText">Processing…</div>
  </div>

@endsection

@push('scripts')
<script src="{{ asset('js/inventory.js') }}"></script>
@endpush