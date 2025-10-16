# Bro's Cafe Management System - Design Guidelines

## Design Approach
**Selected Approach**: Design System (Material Design + Custom Coffee Shop Theming)
**Justification**: As a utility-focused POS and inventory management system, efficiency and data clarity are paramount. Material Design provides robust patterns for data-dense interfaces while allowing customization for brand warmth.

## Core Design Elements

### A. Color Palette

**Light Mode:**
- Primary: 25 75% 40% (Rich coffee brown)
- Secondary: 30 70% 35% (Espresso brown)
- Background: 40 20% 97% (Warm cream)
- Surface: 0 0% 100% (White)
- Text Primary: 25 30% 15%
- Text Secondary: 25 15% 45%
- Success: 140 60% 45%
- Warning: 40 95% 55%
- Error: 0 75% 50%

**Dark Mode:**
- Primary: 25 60% 55% (Warm caramel)
- Secondary: 30 50% 45%
- Background: 25 15% 10% (Dark roast)
- Surface: 25 12% 15%
- Text Primary: 40 20% 95%
- Text Secondary: 40 15% 70%
- Accent (Sparingly): 140 50% 50%

### B. Typography
- **Primary Font**: 'Inter' (Google Fonts) - Clean, modern, excellent readability for data
- **Display Font**: 'Playfair Display' (Google Fonts) - For Bro's Cafe branding elements only
- **Weights**: 400 (regular), 500 (medium), 600 (semibold), 700 (bold)
- **Scale**: text-xs (12px), text-sm (14px), text-base (16px), text-lg (18px), text-xl (20px), text-2xl (24px)

### C. Layout System
**Spacing Primitives**: Use Tailwind units of 2, 4, 6, 8, 12, 16 for consistent rhythm
- Component padding: p-4, p-6
- Section spacing: py-8, py-12
- Card spacing: p-6
- Grid gaps: gap-4, gap-6

### D. Component Library

**Navigation:**
- Sidebar navigation for main app (240px width, collapsible to 64px icons-only)
- Top bar with user profile, notifications, quick actions
- Breadcrumbs for deep navigation hierarchies

**Core UI Elements:**
- Buttons: Solid primary, outline secondary, text tertiary - rounded-lg, h-10 standard
- Input fields: Bordered with focus ring, rounded-md, consistent h-10 height
- Cards: Elevated with subtle shadow, rounded-xl borders
- Tables: Striped rows, sticky headers, sortable columns, row actions on hover
- Badges: Pill-shaped status indicators (order status, stock levels)

**POS Components:**
- Product grid with images, pricing, quick-add buttons
- Shopping cart sidebar (slide-in from right)
- Numeric keypad for cash register
- Payment method selector (Cash, Card, Digital)
- Receipt preview modal

**Dashboard Widgets:**
- Stat cards (4-column grid on desktop, stacked mobile): Sales, Revenue, Orders, Low Stock alerts
- Line charts for sales trends (Chart.js via CDN)
- Bar charts for product performance
- Real-time order status board (Kanban-style columns)

**Order Management:**
- Order cards with color-coded status (pending: blue, preparing: yellow, ready: green, delivery: purple, completed: gray)
- Timeline view for order progression
- Customer details panel (slide-out)
- Print/Export actions

**Inventory System:**
- Data table with inline editing
- Stock level indicators (progress bars)
- Low stock warnings (badge + color coding)
- Quick restock form (modal)

**Forms:**
- Multi-step forms for order placement
- Autocomplete for product search
- Date/time pickers for shift scheduling
- Image upload for product management

### E. Responsive Behavior
- **Desktop (1024px+)**: Full sidebar, multi-column dashboards, expanded tables
- **Tablet (768-1023px)**: Collapsible sidebar, 2-column layouts
- **Mobile (<768px)**: Bottom navigation, single column, swipeable cards

### F. Data Visualization
- Use Chart.js (CDN) for analytics graphs
- Color-code performance metrics (green for above target, yellow for at target, red for below)
- Sparklines for quick trend indicators in stat cards
- Heat maps for peak hours analysis

### G. Customer-Facing Online Ordering
- Hero section with featured items carousel (use coffee/cafe lifestyle images)
- Menu categorization with tabs or accordion
- Product cards with appetizing images, descriptions, pricing
- Floating cart button (bottom-right, shows item count)
- Streamlined checkout flow (3 steps max)

### Images
**Management Dashboard**: No hero image - prioritize data visibility
**Customer Online Ordering**: 
- Hero: Wide lifestyle image of coffee shop interior or latte art (1920x600px)
- Product images: Square format (400x400px) for menu items
- Category headers: Rectangular banners (1200x300px)

**Order Tracking Page**:
- Visual status indicators (icon-based, not image-heavy)
- Optional: Small delivery person illustration for active deliveries

### Animations
Minimal and purposeful only:
- Smooth page transitions (200ms)
- Cart item additions (scale + fade)
- Order status updates (subtle pulse)
- Loading states (skeleton screens, no spinners)
- NO decorative animations

### Accessibility
- WCAG AA contrast ratios minimum
- Keyboard navigation for all POS functions
- Screen reader labels for icon buttons
- Focus indicators on all interactive elements
- Dark mode throughout (forms, inputs, modals)