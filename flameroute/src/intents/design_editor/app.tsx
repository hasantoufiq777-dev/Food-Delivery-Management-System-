import { useFeatureSupport } from "@canva/app-hooks";
import { Button, Rows, Text } from "@canva/app-ui-kit";
import { addPage } from "@canva/design";
import type { TextElementAtPoint } from "@canva/design";
import { useState } from "react";
import * as styles from "styles/components.css";

const slides = [
  {
    title: "FlameRoute",
    subtitle: "Streamlined Food Delivery Logistics & Dashboard",
    bullets: [
      "A unified platform bridging Kitchens, Couriers, Customers, and Administrators.",
      "Fully database-backed system with modular portal interfaces.",
      "Built using modern web aesthetics and Oracle SQL database schemas."
    ]
  },
  {
    title: "Project Overview & Objectives",
    subtitle: "Solving the Last-Mile Delivery Challenge",
    bullets: [
      "Real-time Synchronization: Ensures instant updates between customer order placement, kitchen prep, and courier dispatch.",
      "Role Separation: 4 customized portal dashboards targeting distinct user needs.",
      "Modern Interface: High-fidelity aesthetics replacing outdated legacy dashboards."
    ]
  },
  {
    title: "Modular Portal Architecture",
    subtitle: "Four Interfaces, One Unified Database",
    bullets: [
      "👤 Customer Portal: Browse restaurants, build active carts, place orders, and review delivery.",
      "🍽️ Shop (Restaurant) Portal: Dynamic menu management, item availability, and order prep status.",
      "🛵 Courier (Agent) Portal: Dispatch queue, active routing, and delivery state tracking.",
      "🛡️ Admin Portal: System analytics, simulated user switching, and diagnostics."
    ]
  },
  {
    title: "Database Design Philosophy",
    subtitle: "Robust Relational Foundation in Oracle SQL",
    bullets: [
      "Relational Integrity: Implements rigorous relational modeling in Third Normal Form (3NF).",
      "Robust Datatypes: Standardized Oracle type mapping (NUMBER, VARCHAR2, CLOB, DATE).",
      "System Security: Isolated hashes for passwords and role constraints to ensure authorization."
    ]
  },
  {
    title: "DB Schema: User Identity & Directory",
    subtitle: "Core Table: users",
    bullets: [
      "Structure: user_id (PK), name, email (Unique), password_hash, phone, role, created_at.",
      "Roles Mapping: Identifies if a user is a customer, restaurant, agent, or admin.",
      "Indexation: Fast email-based lookup for authentication routines."
    ]
  },
  {
    title: "DB Schema: Restaurant Profile Management",
    subtitle: "Core Table: restaurants",
    bullets: [
      "Structure: restaurant_id (PK), user_id (FK), name, address (CLOB), cuisine_type, rating, status, image_url.",
      "User Linking: Extends the users table via foreign key constraints (fk_rest_user).",
      "Rating System: Dynamic decimal field (NUMBER(2,1)) with a default value of 0.0."
    ]
  },
  {
    title: "DB Schema: Menu Items & Categories",
    subtitle: "Core Table: menu_items",
    bullets: [
      "Structure: item_id (PK), restaurant_id (FK), name, description (CLOB), price, category, image_url, is_available.",
      "State Control: The boolean numeric is_available flag controls visibility inside customer browses.",
      "Cascading Safety: Menu items automatically delete if the parent restaurant is removed."
    ]
  },
  {
    title: "DB Schema: Delivery Agents (Couriers)",
    subtitle: "Core Table: delivery_agents",
    bullets: [
      "Structure: agent_id (PK), user_id (FK), status, vehicle_type, rating.",
      "Status Enum: Toggles courier state between available, busy, or offline.",
      "Vehicle Typing: Custom profiling for vehicle logistics (e.g., bicycle, motorcycle, car)."
    ]
  },
  {
    title: "DB Schema: Active Shopping Carts",
    subtitle: "Tables: carts & cart_items",
    bullets: [
      "Cart Table: cart_id (PK), customer_id (FK), timestamps for creation and updates.",
      "Cart Items: cart_item_id (PK), cart_id (FK), item_id (FK), quantity.",
      "Transient State: Stores order items before purchase checkout is finalized."
    ]
  },
  {
    title: "DB Schema: Order Transactions",
    subtitle: "Core Table: orders",
    bullets: [
      "Structure: order_id (PK), customer_id (FK), restaurant_id (FK), agent_id (FK), status, total_amount, delivery_address (CLOB).",
      "Order Status States: Defaults to placed, and updates to preparing, ready for pickup, picked up, and delivered.",
      "Nullable Assignee: agent_id is nullable by default until a courier accepts the dispatch."
    ]
  },
  {
    title: "DB Schema: Order Line Items",
    subtitle: "Core Table: order_items",
    bullets: [
      "Structure: order_item_id (PK), order_id (FK), item_id (FK), quantity, unit_price.",
      "Historical Accuracy: Captures the exact price of the item at the time of purchase (unit_price) to insulate records from future menu price updates."
    ]
  },
  {
    title: "DB Schema: Order Lifecycle History",
    subtitle: "Core Table: order_status_history",
    bullets: [
      "Structure: history_id (PK), order_id (FK), status, changed_at, changed_by (FK).",
      "Audit Trail: Tracks exact historical transitions of order states for dispatch analytics.",
      "User Context: Records which entity (Customer, Shop, Admin, or Courier) triggered the state update."
    ]
  },
  {
    title: "DB Schema: Customer Feedback & Reviews",
    subtitle: "Core Table: reviews",
    bullets: [
      "Structure: review_id (PK), customer_id (FK), restaurant_id (FK), order_id (FK), rating, comments (CLOB), created_at.",
      "Relational Loop: Links the reviewer, the target vendor, and the exact order index to ensure rating validity."
    ]
  },
  {
    title: "Database Procedural Logic (PL/SQL)",
    subtitle: "Encapsulating Actions with Stored Procedures",
    bullets: [
      "User Registration: Custom procedure register_user to register and validate customer records.",
      "Transactional Safety: Uses database-level execution blocks to run multi-table inserts.",
      "Consistency: Insulates front-end logic from schema alterations."
    ]
  },
  {
    title: "Referential Integrity & Cascades",
    subtitle: "Strict Constraints for Data Integrity",
    bullets: [
      "Parent-Child Safety: Foreign key constraints restrict orphaned cart items or order rows.",
      "Deletions: Implements ON DELETE CASCADE on carts and items, and ON DELETE SET NULL on courier mappings.",
      "Constraints: Enforces rating boundaries (0 to 5) and unique email constraints."
    ]
  },
  {
    title: "System Aesthetics & Front-end Integration",
    subtitle: "Bringing the Database to Life",
    bullets: [
      "PHP PDO Layer: Connects the Oracle database securely using PDO parameters.",
      "Visual System: Custom Dark/Light palette options built using vanilla CSS variables.",
      "Interactive Testing: Pre-populated simulation credentials for all 4 roles to allow instant feature validation."
    ]
  }
];

export const App = () => {
  const isSupported = useFeatureSupport();
  const [generating, setGenerating] = useState(false);
  const [status, setStatus] = useState("");

  const addPageSupported = isSupported(addPage);

  const generateAllSlides = async () => {
    if (!addPageSupported) return;
    setGenerating(true);
    setStatus("Generating...");

    try {
      for (let i = 0; i < slides.length; i++) {
        const slide = slides[i];
        setStatus(`Creating Slide ${i + 1}/${slides.length}...`);

        await addPage({
          background: {
            color: "#111116",
          },
          elements: [
            {
              type: "text",
              children: [slide.title],
              top: 150,
              left: 150,
              fontSize: 48,
              fontWeight: "bold",
              color: "#ffffff",
              width: 1200,
            } as TextElementAtPoint,
            {
              type: "text",
              children: [slide.subtitle],
              top: 230,
              left: 150,
              fontSize: 24,
              color: "#ff4a4a",
              width: 1200,
            } as TextElementAtPoint,
            {
              type: "text",
              children: [slide.bullets.map((b) => `•  ${b}`).join("\n\n")],
              top: 340,
              left: 150,
              fontSize: 20,
              color: "#e2e2e7",
              width: 1400,
            } as TextElementAtPoint,
          ],
        });
      }
      setStatus("Completed!");
    } catch (e) {
      console.error(e);
      setStatus("Error generating slides");
    } finally {
      setGenerating(false);
    }
  };

  const generateSingleSlide = async (index: number) => {
    if (!addPageSupported) return;
    const slide = slides[index];
    setStatus(`Adding Slide ${index + 1}...`);
    try {
      await addPage({
        background: {
          color: "#111116",
        },
        elements: [
          {
            type: "text",
            children: [slide.title],
            top: 150,
            left: 150,
            fontSize: 48,
            fontWeight: "bold",
            color: "#ffffff",
            width: 1200,
          } as TextElementAtPoint,
          {
            type: "text",
            children: [slide.subtitle],
            top: 230,
            left: 150,
            fontSize: 24,
            color: "#ff4a4a",
            width: 1200,
          } as TextElementAtPoint,
          {
            type: "text",
            children: [slide.bullets.map((b) => `•  ${b}`).join("\n\n")],
            top: 340,
            left: 150,
            fontSize: 20,
            color: "#e2e2e7",
            width: 1400,
          } as TextElementAtPoint,
        ],
      });
      setStatus(`Slide ${index + 1} Added!`);
    } catch (e) {
      console.error(e);
      setStatus("Error adding slide");
    }
  };

  return (
    <div className={styles.scrollContainer}>
      <Rows spacing="2.5u">
        <Text align="center" size="large" tone="critical">
          <strong>FlameRoute Slide Generator</strong>
        </Text>
        <Text align="center" size="small">
          Generate high-fidelity database and system slides directly into your Canva presentation.
        </Text>

        <Button
          variant="primary"
          onClick={generateAllSlides}
          disabled={generating || !addPageSupported}
          stretch
        >
          {generating ? status : "Generate All 16 Slides"}
        </Button>

        {status && (
          <Text align="center" size="small" tone="positive">
            Status: {status}
          </Text>
        )}

        <Rows spacing="1.5u">
          <Text size="small" tone="neutral">
            <strong>Or add individual slides:</strong>
          </Text>
          {slides.map((slide, idx) => (
            <Button
              key={idx}
              variant="secondary"
              onClick={() => generateSingleSlide(idx)}
              disabled={generating || !addPageSupported}
              stretch
            >
              {idx + 1}. {slide.title}
            </Button>
          ))}
        </Rows>
      </Rows>
    </div>
  );
};
