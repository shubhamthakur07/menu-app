import zlib
import os

class SimplePDF:
    def __init__(self, filename):
        self.filename = filename
        self.pages = []
        
    def add_page(self):
        page = Page()
        self.pages.append(page)
        return page

    def save(self):
        out = []
        out.append(b"%PDF-1.4\n")
        offsets = {}
        
        def write_obj(num, data):
            offsets[num] = sum(len(x) for x in out)
            out.append(f"{num} 0 obj\n".encode('ascii'))
            out.append(data)
            out.append(b"\nendobj\n")

        write_obj(1, b"<< /Type /Catalog /Pages 2 0 R >>")
        
        page_refs = [f"{5 + i*2} 0 R" for i in range(len(self.pages))]
        pages_dict = f"<< /Type /Pages /Kids [{ ' '.join(page_refs) }] /Count {len(self.pages)} >>".encode('ascii')
        write_obj(2, pages_dict)
        
        write_obj(3, b"<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>")
        write_obj(4, b"<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>")
        
        for i, page in enumerate(self.pages):
            page_obj_num = 5 + i * 2
            content_obj_num = page_obj_num + 1
            
            stream_data = page.get_stream()
            compressed = zlib.compress(stream_data)
            
            p_dict = f"<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents {content_obj_num} 0 R /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> >>".encode('ascii')
            write_obj(page_obj_num, p_dict)
            
            c_dict = f"<< /Length {len(compressed)} /Filter /FlateDecode >>\nstream\n".encode('ascii') + compressed + b"\nendstream"
            write_obj(content_obj_num, c_dict)
            
        xref_offset = sum(len(x) for x in out)
        num_objs = 5 + len(self.pages) * 2
        out.append(f"xref\n0 {num_objs}\n".encode('ascii'))
        out.append(b"0000000000 65535 f \n")
        for i in range(1, num_objs):
            out.append(f"{offsets[i]:010d} 00000 n \n".encode('ascii'))
            
        out.append(f"trailer\n<< /Size {num_objs} /Root 1 0 R >>\nstartxref\n{xref_offset}\n%%EOF\n".encode('ascii'))
        
        with open(self.filename, 'wb') as f:
            for chunk in out:
                f.write(chunk)
        print(f"Generated PDF successfully: {self.filename}")

class Page:
    def __init__(self):
        self.commands = []
        
    def draw_rect(self, x, y, w, h, fill_rgb=None, stroke_rgb=None, line_width=1):
        cmds = []
        if stroke_rgb:
            cmds.append(f"{stroke_rgb[0]} {stroke_rgb[1]} {stroke_rgb[2]} RG {line_width} w")
        if fill_rgb:
            cmds.append(f"{fill_rgb[0]} {fill_rgb[1]} {fill_rgb[2]} rg")
        cmds.append(f"{x} {y} {w} {h} re")
        if fill_rgb and stroke_rgb:
            cmds.append("B")
        elif fill_rgb:
            cmds.append("f")
        elif stroke_rgb:
            cmds.append("S")
        self.commands.append(" ".join(cmds))
        
    def add_text(self, text, x, y, font="F1", size=10, color_rgb=(0,0,0)):
        safe = text.replace('\\', '\\\\').replace('(', '\\(').replace(')', '\\)')
        r, g, b = color_rgb
        cmd = f"BT {r} {g} {b} rg /{font} {size} Tf {x} {y} Td ({safe}) Tj ET"
        self.commands.append(cmd)
        
    def add_multiline(self, lines, x, start_y, line_gap=14, font="F1", size=10, color_rgb=(0.2,0.2,0.2)):
        cur_y = start_y
        for line in lines:
            self.add_text(line, x, cur_y, font=font, size=size, color_rgb=color_rgb)
            cur_y -= line_gap
        return cur_y

    def get_stream(self):
        return "\n".join(self.commands).encode('latin-1', 'replace')


def build_guide_pdf(output_path):
    pdf = SimplePDF(output_path)
    
    # -------------------------------------------------------------------------
    # PAGE 1: TITLE & EXECUTIVE SUMMARY
    # -------------------------------------------------------------------------
    p1 = pdf.add_page()
    # Background
    p1.draw_rect(0, 0, 595, 842, fill_rgb=(0.97, 0.98, 1.0))
    # Header Banner
    p1.draw_rect(30, 710, 535, 100, fill_rgb=(0.08, 0.12, 0.22), stroke_rgb=(0.9, 0.6, 0.15), line_width=2)
    p1.add_text("SMART RESTAURANT QR MENU & ORDER SYSTEM", 50, 765, font="F2", size=17, color_rgb=(1.0, 0.72, 0.2))
    p1.add_text("Complete Non-Technical User Manual & Operational Guide", 50, 742, font="F1", size=11, color_rgb=(0.9, 0.93, 0.98))
    p1.add_text("Designed for Restaurant Owners, Managers, Kitchen Staff & Customers", 50, 725, font="F1", size=9, color_rgb=(0.65, 0.75, 0.9))

    # Section 1: What is this System?
    p1.draw_rect(30, 550, 535, 145, fill_rgb=(1, 1, 1), stroke_rgb=(0.85, 0.88, 0.92), line_width=1)
    p1.draw_rect(30, 665, 535, 30, fill_rgb=(0.93, 0.95, 0.99))
    p1.add_text("1. WHAT IS THIS APPLICATION? (SYSTEM INTRODUCTION)", 45, 675, font="F2", size=12, color_rgb=(0.1, 0.2, 0.4))
    
    lines_p1_sec1 = [
        "This is an end-to-end Smart Digital Restaurant Ordering and Management Platform.",
        "It replaces old paper menus with interactive QR codes placed on dining tables.",
        "",
        "Key Highlights:",
        " * No App Download Needed: Customers simply open camera, scan QR, and browse the menu.",
        " * Live Kitchen Alerts: Automatic audio chimes and popups when food starts cooking or gets ready.",
        " * Instant Payments: Integrated UPI QR Scanner (GPay, PhonePe, Paytm) + Cash at counter.",
        " * Home Delivery & Table Dine-in: Owner can enable or disable delivery mode anytime.",
        " * Live Table Occupancy: Automatically prevents duplicate ordering on currently occupied tables."
    ]
    p1.add_multiline(lines_p1_sec1, 45, 645, line_gap=13, font="F1", size=9.5, color_rgb=(0.2, 0.25, 0.3))

    # Section 2: Key Business Benefits (Why use this?)
    p1.draw_rect(30, 375, 535, 160, fill_rgb=(1, 1, 1), stroke_rgb=(0.85, 0.88, 0.92), line_width=1)
    p1.draw_rect(30, 505, 535, 30, fill_rgb=(0.93, 0.95, 0.99))
    p1.add_text("2. BUSINESS BENEFITS FOR RESTAURANT OWNERS", 45, 515, font="F2", size=12, color_rgb=(0.1, 0.2, 0.4))

    benefits = [
        " 1. Faster Table Turnaround: Customers order immediately without waiting for a waiter.",
        " 2. Zero Human Error: Exact customer cooking notes (e.g. 'less spicy') go straight to the chef.",
        " 3. Save Printing Costs: Add new dishes, change prices, or mark dishes out of stock instantly.",
        " 4. Interactive Waiting Games: Customers play games on their phone while waiting for food,",
        "    keeping them entertained and unlocking instant 5% dining discounts.",
        " 5. Complete Live Visibility: Watch pending, cooking, ready, and completed orders from 1 screen.",
        " 6. 100% Free Cloud Infrastructure: Powered by serverless databases with zero monthly cost."
    ]
    p1.add_multiline(benefits, 45, 485, line_gap=14, font="F1", size=9.5, color_rgb=(0.15, 0.2, 0.25))

    # Section 3: User Roles & Access
    p1.draw_rect(30, 190, 535, 170, fill_rgb=(1, 1, 1), stroke_rgb=(0.85, 0.88, 0.92), line_width=1)
    p1.draw_rect(30, 330, 535, 30, fill_rgb=(0.93, 0.95, 0.99))
    p1.add_text("3. WHO CAN USE THIS SYSTEM? (USER ROLES)", 45, 340, font="F2", size=12, color_rgb=(0.1, 0.2, 0.4))

    roles = [
        " [A] DINE-IN CUSTOMERS (TABLE GUESTS)",
        "     - Scan Table QR -> View Menu with HD images -> Place order -> Pay online or cash.",
        "",
        " [B] KITCHEN CHEF & WAITSTAFF",
        "     - Hear audio chimes when new orders arrive -> Click 'In Kitchen' -> Click 'Food Ready'.",
        "",
        " [C] RESTAURANT OWNER & CASHIER",
        "     - Monitor live revenue, mark cash payments, toggle delivery, and print QR codes."
    ]
    p1.add_multiline(roles, 45, 310, line_gap=13, font="F1", size=9.5, color_rgb=(0.15, 0.2, 0.25))

    # Page Footer
    p1.add_text("Page 1 of 4  *  Smart Restaurant QR Menu User Guide", 210, 40, font="F1", size=9, color_rgb=(0.5, 0.55, 0.6))

    # -------------------------------------------------------------------------
    # PAGE 2: CUSTOMER ORDERING JOURNEY
    # -------------------------------------------------------------------------
    p2 = pdf.add_page()
    p2.draw_rect(0, 0, 595, 842, fill_rgb=(0.97, 0.98, 1.0))
    p2.draw_rect(30, 755, 535, 55, fill_rgb=(0.08, 0.12, 0.22), stroke_rgb=(0.9, 0.6, 0.15), line_width=2)
    p2.add_text("PART 1: CUSTOMER ORDERING EXPERIENCE", 50, 785, font="F2", size=15, color_rgb=(1.0, 0.72, 0.2))
    p2.add_text("Step-by-step workflow for guests dining in the restaurant or ordering online", 50, 768, font="F1", size=10, color_rgb=(0.9, 0.93, 0.98))

    # Step Cards
    steps = [
        ("STEP 1: SCANNING THE QR CODE", [
            "Customer sits at Table (e.g. Table 1) and points their phone camera at the QR standee.",
            "A link opens instantly in Chrome or Safari (no app install or signup required).",
            "The system automatically binds the customer session to Table 1."
        ]),
        ("STEP 2: BROWSING MENU & ADDING DISHES", [
            "Categories are tabbed at top: All, Starters, Main Course, Breads, Desserts, Beverages.",
            "Items display Veg/Non-Veg icons, prices, descriptions, and high-definition photos.",
            "Tap '+' button to add items into the cart with live subtotal calculation."
        ]),
        ("STEP 3: CHECKOUT & SPECIAL REQUESTS", [
            "Customer enters their Name and Mobile Number (remembered automatically for next time).",
            "Special Cooking Notes: e.g., 'Less spicy, extra lemons, send hot' - goes straight to chef.",
            "Choose Payment Option: 'Pay via UPI/Online' OR 'Pay Cash at Counter'."
        ]),
        ("STEP 4: LIVE STATUS TRACKING & WAITING GAMES", [
            "Instant Order Code generated (e.g. #ORD-JXT117) with live status timeline:",
            " * Received -> Preparing (In Kitchen) -> Food Ready -> Served / Completed",
            "Waiting Game Challenge: Customers can play 'Triple Match' & 'Speed Reflex' while food",
            "is being cooked. Completing the 2 games awards an instant 5% bill discount!"
        ]),
        ("STEP 5: RE-ORDERING (ADDING MORE ITEMS)", [
            "If guests want extra rotis, drinks, or desserts, they simply tap '+ Order More Items'.",
            "Their details are already pre-filled, preventing any duplicate table confusion!"
        ])
    ]

    y_pos = 730
    for title, desc_lines in steps:
        box_h = 24 + len(desc_lines) * 14 + 10
        p2.draw_rect(30, y_pos - box_h, 535, box_h, fill_rgb=(1, 1, 1), stroke_rgb=(0.82, 0.86, 0.92), line_width=1)
        p2.draw_rect(30, y_pos - 24, 535, 24, fill_rgb=(0.92, 0.95, 0.99))
        p2.add_text(title, 45, y_pos - 16, font="F2", size=11, color_rgb=(0.1, 0.25, 0.5))
        p2.add_multiline(desc_lines, 45, y_pos - 38, line_gap=13, font="F1", size=9.5, color_rgb=(0.2, 0.25, 0.3))
        y_pos -= (box_h + 12)

    p2.add_text("Page 2 of 4  *  Smart Restaurant QR Menu User Guide", 210, 40, font="F1", size=9, color_rgb=(0.5, 0.55, 0.6))

    # -------------------------------------------------------------------------
    # PAGE 3: RESTAURANT OWNER & KITCHEN DASHBOARD
    # -------------------------------------------------------------------------
    p3 = pdf.add_page()
    p3.draw_rect(0, 0, 595, 842, fill_rgb=(0.97, 0.98, 1.0))
    p3.draw_rect(30, 755, 535, 55, fill_rgb=(0.08, 0.12, 0.22), stroke_rgb=(0.9, 0.6, 0.15), line_width=2)
    p3.add_text("PART 2: RESTAURANT OWNER & KITCHEN MANAGEMENT", 50, 785, font="F2", size=15, color_rgb=(1.0, 0.72, 0.2))
    p3.add_text("How staff and owners operate the dashboard, manage orders, and collect payments", 50, 768, font="F1", size=10, color_rgb=(0.9, 0.93, 0.98))

    admin_sections = [
        ("1. LIVE ORDERS SCREEN (REAL-TIME NOTIFICATIONS)", [
            " * Auto-Refresh: Dashboard polls automatically every 4 seconds without manual reloads.",
            " * Audio Chime: Distinct bell chime sounds as soon as a new order arrives.",
            " * Notification Banner: Shows order number, table, customer name, and total amount.",
            " * 'Enable Alerts' Toggle: Click this button once in top bar to turn on sound & alerts."
        ]),
        ("2. KITCHEN ORDER STATUS LIFECYCLE (ONE-CLICK BUTTONS)", [
            "Each order card has quick operational buttons with instant response:",
            "  [In Kitchen]: Click when chef starts preparing. Customer sees: 'Cooking in progress!'.",
            "  [Food Ready]: Click when dishes are ready on counter. Customer hears chime: 'Food Ready!'.",
            "  [Served]: Click when waiter delivers dishes to table. Order moves to completed.",
            "  [Mark Paid]: Click when customer pays cash at the counter to clear the bill."
        ]),
        ("3. FLOOR & TABLE MANAGEMENT", [
            " * View all tables at a glance (e.g., Table 1 to Table 10).",
            " * Visual occupancy badges: Green = Available / Free, Red = Currently Occupied.",
            " * 'Table QR Generator': Generates custom printable PDF QR standees for every table.",
            "   Simply download, print, laminate, and place on tables."
        ]),
        ("4. MENU DISHES & CATEGORIES MANAGER", [
            " * Add Dishes: Upload photo, enter name, category, price, veg/non-veg, and description.",
            " * Out of Stock Toggle: Turn dishes temporarily ON/OFF with 1 click if ingredients finish.",
            " * Category Ordering: Organize dishes into Starters, Main Course, Drinks, etc."
        ]),
        ("5. RESTAURANT SETTINGS & DELIVERY TOGGLE", [
            " * Home Delivery Setting: Owner can turn Home Delivery ON or OFF anytime.",
            " * UPI QR Setup: Enter restaurant UPI ID (e.g. hotel@okhdfcbank) to receive online payments.",
            " * Operating Timings & Taxes: Configure GST/Tax percentage and opening/closing hours."
        ])
    ]

    y_pos = 730
    for title, desc_lines in admin_sections:
        box_h = 24 + len(desc_lines) * 14 + 10
        p3.draw_rect(30, y_pos - box_h, 535, box_h, fill_rgb=(1, 1, 1), stroke_rgb=(0.82, 0.86, 0.92), line_width=1)
        p3.draw_rect(30, y_pos - 24, 535, 24, fill_rgb=(0.92, 0.95, 0.99))
        p3.add_text(title, 45, y_pos - 16, font="F2", size=11, color_rgb=(0.1, 0.25, 0.5))
        p3.add_multiline(desc_lines, 45, y_pos - 38, line_gap=13, font="F1", size=9.5, color_rgb=(0.2, 0.25, 0.3))
        y_pos -= (box_h + 10)

    p3.add_text("Page 3 of 4  *  Smart Restaurant QR Menu User Guide", 210, 40, font="F1", size=9, color_rgb=(0.5, 0.55, 0.6))

    # -------------------------------------------------------------------------
    # PAGE 4: FAQ & TROUBLESHOOTING GUIDE
    # -------------------------------------------------------------------------
    p4 = pdf.add_page()
    p4.draw_rect(0, 0, 595, 842, fill_rgb=(0.97, 0.98, 1.0))
    p4.draw_rect(30, 755, 535, 55, fill_rgb=(0.08, 0.12, 0.22), stroke_rgb=(0.9, 0.6, 0.15), line_width=2)
    p4.add_text("PART 3: FREQUENTLY ASKED QUESTIONS (FAQ)", 50, 785, font="F2", size=15, color_rgb=(1.0, 0.72, 0.2))
    p4.add_text("Common scenarios, staff guidelines, and quick answers for daily restaurant operations", 50, 768, font="F1", size=10, color_rgb=(0.9, 0.93, 0.98))

    faqs = [
        ("Q1: Do customers have to install an app from Google Play or Apple App Store?", [
            "A: NO. That is the biggest benefit! Customers simply open their default phone camera,",
            "scan the table QR standee, and the menu opens immediately in their browser."
        ]),
        ("Q2: How does the kitchen get notified when a new order comes in?", [
            "A: The Owner Dashboard plays a distinct audio chime (bell sound) and pops up a banner",
            "at the top of the screen showing the table number and items. Simply keep the dashboard",
            "open on a tablet or laptop in the counter/kitchen area."
        ]),
        ("Q3: What if a customer pays via UPI (Google Pay, PhonePe, Paytm)?", [
            "A: When the customer selects 'Pay via UPI', an instant Dynamic QR code is generated on their",
            "phone. Once completed, the customer screen and owner dashboard automatically turn green",
            "with a 'Payment Completed' success chime."
        ]),
        ("Q4: Can a customer add more items while eating without starting a new bill?", [
            "A: YES. They simply click '+ Order More Items' on their tracking screen. Their table and",
            "customer details remain saved, and new items append directly to their order."
        ]),
        ("Q5: What if Table 1 is occupied and someone else tries to order on Table 1?", [
            "A: The system automatically detects active occupancy. If another guest attempts to order",
            "on the same table while an order is ongoing, it prompts them to select a free table."
        ]),
        ("Q6: How does the Owner toggle Home Delivery?", [
            "A: In the Owner Dashboard -> 'Settings & Timings' -> toggle 'Allow Home Delivery'.",
            "When turned OFF, customers can only choose Dine-in or Takeaway."
        ])
    ]

    y_pos = 730
    for q_title, a_lines in faqs:
        box_h = 24 + len(a_lines) * 14 + 8
        p4.draw_rect(30, y_pos - box_h, 535, box_h, fill_rgb=(1, 1, 1), stroke_rgb=(0.82, 0.86, 0.92), line_width=1)
        p4.draw_rect(30, y_pos - 22, 535, 22, fill_rgb=(0.95, 0.96, 0.99))
        p4.add_text(q_title, 45, y_pos - 15, font="F2", size=10, color_rgb=(0.15, 0.3, 0.6))
        p4.add_multiline(a_lines, 45, y_pos - 35, line_gap=13, font="F1", size=9, color_rgb=(0.25, 0.28, 0.32))
        y_pos -= (box_h + 10)

    # Emergency Contact / Help Box
    p4.draw_rect(30, 80, 535, 65, fill_rgb=(0.92, 0.97, 0.93), stroke_rgb=(0.3, 0.7, 0.4), line_width=1.5)
    p4.add_text("QUICK TIP FOR RESTAURANT STAFF:", 45, 125, font="F2", size=10.5, color_rgb=(0.1, 0.45, 0.2))
    p4.add_text("Keep a dedicated Android tablet or iPad at the billing counter with sound volume high.", 45, 108, font="F1", size=9.5, color_rgb=(0.15, 0.35, 0.2))
    p4.add_text("Click 'Enable Alerts' once at the start of the day to ensure real-time audio rings for every order.", 45, 93, font="F1", size=9.5, color_rgb=(0.15, 0.35, 0.2))

    p4.add_text("Page 4 of 4  *  Smart Restaurant QR Menu User Guide", 210, 40, font="F1", size=9, color_rgb=(0.5, 0.55, 0.6))

    pdf.save()

if __name__ == '__main__':
    dest = '/var/www/html/menu app/public/Smart_Restaurant_QR_Menu_User_Guide.pdf'
    build_guide_pdf(dest)
