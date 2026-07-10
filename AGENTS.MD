# AGENTS.md — Loan Management System

Instructions for any AI coding agent (or human) working in this repo. Read this before writing code.

## Project Summary

A PHP + MySQL web app for financial institutions to manage customer records, loan applications, a rule-based eligibility prediction, approvals, repayments, and reporting. Full spec: see the original Chapter 1–4 write-up and `TODO.md` for the build order. `database/schema.sql` is the source of truth for the data model — don't invent columns or tables that aren't there without updating it first.

## Stack & Conventions

- **Backend:** PHP (procedural or lightly OOP, no framework assumed unless already introduced)
- **DB:** MySQL, accessed via mysqli or PDO with **prepared statements only** — never concatenate user input into SQL
- **Frontend:** HTML, CSS, Bootstrap, vanilla JS (no build step / no npm framework unless the project has already moved to one)
- **File layout:** follow the structure already agreed — `config/`, `includes/`, `assets/`, `modules/<feature>/`, `database/`
- **Sessions:** `session_start()` + `$_SESSION['admin_id']` gate for every page under `modules/` except `login.php`
- **Naming:** snake_case for PHP files and DB columns (matches `schema.sql`), kebab-case for CSS classes
- Every form: server-side validation always, client-side validation as a UX nicety on top — never the only line of defense
- Money values: `DECIMAL`, never float, in both DB and PHP calculations

## UI Design System

This is a back-office financial tool used daily by loan officers and admins — optimize for legibility, scan-speed, and trust over decoration. It should feel like a solid Nigerian fintech back-office (think Kuda/Moniepoint ops tooling), not a marketing site, and not a generic Bootstrap-default admin theme.

### Color

| Token | Hex | Use |
|---|---|---|
| `--ink` | `#12251E` | Primary text, headers |
| `--paper` | `#F7F8F4` | App background |
| `--surface` | `#FFFFFF` | Cards, tables, panels |
| `--forest` | `#1B4332` | Primary brand — nav, primary buttons, links |
| `--gold` | `#C99A3B` | Accent — highlights, active states, key figures |
| `--line` | `#E1E4DD` | Borders, dividers, table rules |

Status colors (loan/repayment states — used only for status badges and indicators, never as large fills):

| Status | Color |
|---|---|
| Approved / Completed | `#1B7A43` (green) |
| Pending | `#B8860B` (amber) |
| Rejected | `#B3261E` (red) |
| On Hold | `#5C5F66` (gray) |

Rationale: forest green + muted gold reads as "money handled seriously" without falling back on generic fintech blue or the cliché terracotta/cream AI-default palette. Status colors are desaturated, not neon, so a table full of badges stays calm and readable.

### Typography

- **UI / body face:** Inter (system fallback: `-apple-system, Segoe UI, sans-serif`) — set at 14–15px base for dense tables
- **Numeric / data face:** Inter with `font-variant-numeric: tabular-nums` on every amount, balance, and ID column so figures align in tables
- **Display face (dashboard headings, report titles only):** a single characterful serif, e.g. Fraunces or Lora, used sparingly — page titles and report headers only, never body copy or table content
- Type scale: `12 / 14 / 16 / 20 / 28 / 36` px. Don't introduce sizes outside this scale.
- Weights: 400 body, 500 for labels/table headers, 600 for headings and monetary totals

### Layout

```
┌──────────┬────────────────────────────────────┐
│          │  Topbar: page title · admin name    │
│ Sidebar  ├────────────────────────────────────┤
│  (nav)   │                                      │
│          │  Content area (cards / tables)       │
│  forest  │  paper background, 24px gutter       │
│  bg      │                                      │
└──────────┴────────────────────────────────────┘
```

- Fixed left sidebar (forest `--forest` background, white text), collapses to icon-only under 768px
- Dashboard = card grid (4-up desktop, 2-up tablet, 1-up mobile) for summary stats, followed by a recent-activity table
- List/report pages = table-first, filters in a slim bar above the table, no ornamental hero sections — this is a tool, not a landing page
- Forms = single column, max-width ~640px, label above input, inline validation messages directly under the field
- 8px spacing unit throughout (`8 / 16 / 24 / 32 / 48`); don't freehand arbitrary padding values

### Components

- **Buttons:** solid `--forest` for primary actions ("Approve", "Save"), outline for secondary ("Cancel"), text-only red for destructive ("Reject", "Delete") — always name the action, never "Submit"
- **Status badge:** small pill, colored text on a 10%-opacity tint of the same color, not solid fill
- **Tables:** zebra-free, `--line` bottom-border rows only, sticky header on scroll, right-align all numeric columns
- **Cards:** `--surface` background, 1px `--line` border, no drop shadow (or a barely-there 1px one) — flat, not skeuomorphic
- **Empty states:** one line explaining what's missing + the primary action to fix it (e.g. "No loans yet. Register a customer to get started.") — never just a blank table
- **Prediction result:** shown as a labeled badge ("Eligible" / "Not Eligible") next to, not instead of, the underlying figures it was computed from — it's a decision aid, never hide the inputs behind the verdict

### Motion

Minimal. A 150ms ease-out on hover/focus states and panel transitions is enough. No page-load animation, no scroll effects — this app is opened dozens of times a day by the same people; novelty motion becomes friction.

### Accessibility floor (non-negotiable)

- Visible keyboard focus ring on every interactive element (don't `outline: none` without replacing it)
- Color is never the only status signal — pair every status badge with text, not color alone
- Forms: every input has a real `<label>`, errors are announced in text next to the field
- Contrast: body text ≥ 4.5:1 against its background at all times

## What NOT to do

- Don't reach for the generic Bootstrap default admin-theme look (blue navbar, default card shadows) — use the tokens above
- Don't introduce a JS framework or build step unless explicitly asked
- Don't store money as float or plaintext passwords
- Don't build UI for a module before its table/relations exist in `schema.sql`