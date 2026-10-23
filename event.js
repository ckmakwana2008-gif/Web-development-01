/* ==========================================================================
   StudentHub Asynchronous Data Fetching, Search, Filter & Pagination Engine
   Course Code: ITUE203 - Practical 06
   ========================================================================== */

(() => {
  // Application State Store
  const state = {
    rawData: [],
    filteredData: [],
    searchQuery: "",
    selectedCategory: "All",
    sortOption: "date-asc",
    currentPage: 1,
    pageSize: 6,
    isLoading: false,
    error: null
  };

  // DOM Elements
  const DOM = {
    searchInput: document.getElementById("search-input"),
    categoryFilter: document.getElementById("category-filter"),
    sortOrder: document.getElementById("sort-order"),
    statusContainer: document.getElementById("status-container"),
    eventsGrid: document.getElementById("events-grid"),
    pagination: document.getElementById("pagination")
  };

  // 1. Asynchronous Data Fetching
  const fetchEventsData = async () => {
    setLoadingState(true);
    try {
      const response = await fetch("events.json");
      if (!response.ok) {
        throw new Error(`HTTP Error! Status: ${response.status} (${response.statusText})`);
      }
      const data = await response.json();
      state.rawData = data;
      state.error = null;
      applyFiltersAndRender();
    } catch (err) {
      state.error = `Failed to load events data: ${err.message}`;
      renderErrorState(state.error);
    } finally {
      setLoadingState(false);
    }
  };

  // 2. State Indicators (Loading / Error)
  const setLoadingState = (loading) => {
    state.isLoading = loading;
    if (loading) {
      DOM.statusContainer.innerHTML = `<div class="loading-spinner"><p>Loading campus events, please wait...</p></div>`;
      DOM.eventsGrid.innerHTML = "";
      DOM.pagination.innerHTML = "";
    } else if (!state.error) {
      DOM.statusContainer.innerHTML = "";
    }
  };

  const renderErrorState = (msg) => {
    DOM.statusContainer.innerHTML = `<div class="alert alert-danger" role="alert"><p>⚠️ ${msg}</p></div>`;
    DOM.eventsGrid.innerHTML = "";
    DOM.pagination.innerHTML = "";
  };

  // 3. Data Processing: Search, Filter, Sort
  const applyFiltersAndRender = () => {
    let result = [...state.rawData];

    // Search Filter (Title & Description matching)
    if (state.searchQuery.trim() !== "") {
      const query = state.searchQuery.toLowerCase();
      result = result.filter((item) =>
        item.title.toLowerCase().includes(query) ||
        item.description.toLowerCase().includes(query) ||
        item.location.toLowerCase().includes(query)
      );
    }

    // Category Filter
    if (state.selectedCategory !== "All") {
      result = result.filter((item) => item.category === state.selectedCategory);
    }

    // Sorting Logic
    result.sort((a, b) => {
      switch (state.sortOption) {
        case "date-asc":
          return new Date(a.date) - new Date(b.date);
        case "date-desc":
          return new Date(b.date) - new Date(a.date);
        case "title-asc":
          return a.title.localeCompare(b.title);
        case "title-desc":
          return b.title.localeCompare(a.title);
        default:
          return 0;
      }
    });

    state.filteredData = result;
    state.currentPage = 1; // Reset to page 1 upon query change
    renderView();
  };

  // 4. Pagination & DOM Rendering Engine
  const renderView = () => {
    const totalItems = state.filteredData.length;

    // Handle Empty Results
    if (totalItems === 0) {
      DOM.eventsGrid.innerHTML = "";
      DOM.pagination.innerHTML = "";
      DOM.statusContainer.innerHTML = `<div class="alert alert-info"><p>No events found matching your filter criteria.</p></div>`;
      return;
    } else {
      DOM.statusContainer.innerHTML = "";
    }

    // Calculate Pagination Boundaries
    const totalPages = Math.ceil(totalItems / state.pageSize);
    const startIndex = (state.currentPage - 1) * state.pageSize;
    const endIndex = startIndex + state.pageSize;
    const pageItems = state.filteredData.slice(startIndex, endIndex);

    // Render Dynamic Cards
    DOM.eventsGrid.innerHTML = pageItems.map((event) => `
      <li class="card">
        <span class="badge badge-category">${escapeHTML(event.category)}</span>
        <h3>${escapeHTML(event.title)}</h3>
        <p class="event-meta">📅 <strong>Date:</strong> ${escapeHTML(event.date)} | 📍 <strong>Venue:</strong> ${escapeHTML(event.location)}</p>
        <p>${escapeHTML(event.description)}</p>
      </li>
    `).join("");

    // Render Pagination Controls
    renderPaginationControls(totalPages);
  };

  // Pagination Control Renderer
  const renderPaginationControls = (totalPages) => {
    if (totalPages <= 1) {
      DOM.pagination.innerHTML = "";
      return;
    }

    let buttonsHTML = `
      <button class="btn btn-sm" id="prev-page" ${state.currentPage === 1 ? "disabled" : ""} aria-label="Previous Page">← Prev</button>
      <span class="page-indicator">Page <strong>${state.currentPage}</strong> of <strong>${totalPages}</strong></span>
      <button class="btn btn-sm" id="next-page" ${state.currentPage === totalPages ? "disabled" : ""} aria-label="Next Page">Next →</button>
    `;

    DOM.pagination.innerHTML = buttonsHTML;

    // Bind Pagination Listeners
    document.getElementById("prev-page")?.addEventListener("click", () => {
      if (state.currentPage > 1) {
        state.currentPage--;
        renderView();
      }
    });

    document.getElementById("next-page")?.addEventListener("click", () => {
      if (state.currentPage < totalPages) {
        state.currentPage++;
        renderView();
      }
    });
  };

  // Utility XSS Prevention Helper
  const escapeHTML = (str) => {
    return String(str).replace(/[&<>"']/g, (m) => ({
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#039;"
    }[m]));
  };

  // 5. Event Listener Binding
  const setupEventListeners = () => {
    // Search Input with Debounce
    let debounceTimer;
    DOM.searchInput?.addEventListener("input", (e) => {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => {
        state.searchQuery = e.target.value;
        applyFiltersAndRender();
      }, 300);
    });

    // Category Filter Dropdown
    DOM.categoryFilter?.addEventListener("change", (e) => {
      state.selectedCategory = e.target.value;
      applyFiltersAndRender();
    });

    // Sort Order Dropdown
    DOM.sortOrder?.addEventListener("change", (e) => {
      state.sortOption = e.target.value;
      applyFiltersAndRender();
    });
  };

  // Initializer
  const init = () => {
    setupEventListeners();
    fetchEventsData();
  };

  document.addEventListener("DOMContentLoaded", init);
})();
