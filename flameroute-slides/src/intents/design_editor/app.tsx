import { useFeatureSupport } from "@canva/app-hooks";
import { Button, Rows, Text, Box, Title } from "@canva/app-ui-kit";
import { addElementAtCursor, addElementAtPoint } from "@canva/design";
import * as styles from "styles/components.css";

const slides = [
  {
    num: 1,
    title: "FlameRoute: Backend Relocation & Modern UX Overhaul",
    bullets: [
      "Transforming a Mock-Session PHP App into a Production-Ready Oracle Relational Platform",
      "Senior UI/UX Engineer & Database Architect"
    ]
  },
  {
    num: 2,
    title: "Moving Beyond Mock Sessions",
    bullets: [
      "The Starting Point: A fully functional Pure PHP application relying entirely on $_SESSION simulator arrays.",
      "The Challenge: Upgrading to a professional relational database and rebuilding the visual design without breaking existing business logic.",
      "The Design Constraint: Redesigning the entire presentation layer while keeping PHP routing, forms, variables, and session models intact."
    ]
  },
  {
    num: 3,
    title: "Oracle Relational Schema Design",
    bullets: [
      "Unified Accounts: users table serving Customers, Restaurants, Couriers, and Admins.",
      "Transactional Integrity: carts, cart_items, orders, and order_items tables.",
      "Entity Relationships: Strict foreign key constraints with cascade deletes mapping menu dishes to partner kitchens.",
      "Data Types: Leveraging CLOBs for text-intensive content like delivery addresses."
    ]
  },
  {
    num: 4,
    title: "Stored Procedures & Transaction Controls",
    bullets: [
      "REGISTER_USER: Secure password storage, account indexing, and profile table instantiation.",
      "ADD_TO_CART: Safe quantity tracking for multiple database shoppers.",
      "PLACE_ORDER_FROM_CART: Single-transaction cart checkout and subtotal calculations.",
      "UPDATE_ORDER_STATUS: Updates transit states and courier availability.",
      "SUBMIT_REVIEW: Real-time aggregation of restaurant star-ratings."
    ]
  },
  {
    num: 5,
    title: "Appetite-Focused Soft Orange Design System",
    bullets: [
      "Aesthetics: Replaced cold default themes with a modern Soft Orange (#FB923C) and Muted Amber (#D97706) layout.",
      "Contrast Compliance: Applied high-contrast dark brown text (#5C2308) directly on orange fields for strict WCAG AA legibility.",
      "Typography: Swapped browser fonts for Poppins (headings) and Inter (body text).",
      "Grid Layouts: Implemented split-column screens with sticky payment summaries."
    ]
  },
  {
    num: 6,
    title: "Context-Aware Status Badges",
    bullets: [
      "Time-Sensitive Active (Out for Delivery): Bold soft orange badge to draw instant user attention.",
      "Low-Urgency Active (Preparing / Ready): Warm, low-contrast pale orange tint (#FEF0E4 background + #9A5B12 text).",
      "Neutral Completed (Delivered): Subdued light gray badge to prevent visual clutter.",
      "Terminated (Cancelled): Muted red tint indicating warnings."
    ]
  },
  {
    num: 7,
    title: "Key Architecture & Driver Workarounds",
    bullets: [
      "Case-Insensitive Logins: Enabled LOWER(email) = LOWER(:email) queries to resolve user registration login failures.",
      "Oci8 Parameter Binding: Switched procedure execution to positional binding (?) to prevent ORA-01745 reserved keyword conflicts.",
      "CLOB Stream Parsing: Programmed stream readers directly inside the database normalizer.",
      "Output Buffering: Activated global ob_start() in the header to allow clean redirects."
    ]
  },
  {
    num: 8,
    title: "Interactive Data Synchronization",
    bullets: [
      "Live Cart Count: Dynamic cart badge counts synced directly to the Oracle database cart.",
      "Completed Courier Logs: Live trip counts updated dynamically in the Admin dashboard via orders tracking.",
      "Hybrid Address Persistence: Session and order database overrides ensuring addresses remain saved for new customers."
    ]
  },
  {
    num: 9,
    title: "Conclusion",
    bullets: [
      "The system is now 100% production-ready, transactional, and runs on a beautiful, modern theme.",
      "Backend operations are fully decoupled into Oracle storage procedures.",
      "Modern, AA-accessible dashboard aesthetics are ready for real-world customer usage."
    ]
  }
];

export const App = () => {
  const isSupported = useFeatureSupport();
  const addElement = [addElementAtPoint, addElementAtCursor].find((fn) =>
    isSupported(fn),
  );

  const insertSlideText = (title: string, bullets: string[]) => {
    if (!addElement) return;

    // Insert title
    addElement({
      type: "text",
      children: [title],
    });

    // Insert bullets
    addElement({
      type: "text",
      children: [bullets.map((b) => `• ${b}`).join("\n")],
    });
  };

  return (
    <div className={styles.scrollContainer}>
      <Rows spacing="3u">
        <Box background="toast" padding="2u" borderRadius="large">
          <Title size="medium" align="center">FlameRoute Presenter</Title>
          <Text align="center" size="small" tone="neutral">
            Click any slide below to insert its title and bullets directly into your design!
          </Text>
        </Box>
        
        {slides.map((s) => (
          <Box key={s.num} border="solid" borderRadius="large" padding="2u" background="standard">
            <Rows spacing="1.5u">
              <Text weight="bold" size="medium">
                {s.num}. {s.title}
              </Text>
              
              <Box paddingStart="1u">
                <Rows spacing="0.5u">
                  {s.bullets.slice(0, 2).map((b, idx) => (
                    <Text key={idx} size="small" tone="neutral">
                      • {b.length > 50 ? `${b.substring(0, 50)}...` : b}
                    </Text>
                  ))}
                  {s.bullets.length > 2 && (
                    <Text size="small" tone="neutral" italic>
                      + {s.bullets.length - 2} more points
                    </Text>
                  )}
                </Rows>
              </Box>

              <Button
                variant="primary"
                onClick={() => insertSlideText(s.title, s.bullets)}
                disabled={!addElement}
                stretch
              >
                Insert Slide Text
              </Button>
            </Rows>
          </Box>
        ))}
      </Rows>
    </div>
  );
};
