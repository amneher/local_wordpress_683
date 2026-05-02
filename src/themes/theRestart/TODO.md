3.  wp:button blocks in 404.html have a spurious url attribute

The url key does not exist in the wp:button block schema — the  
 URL belongs only on the <a> tag in the HTML. The href values in the HTML are
correct, but the block comment attribute will cause validator warnings.

---

Block Grammar Issues

4. wp:post-author uses bylineText — should be byline

In single.html: "bylineText":"By" is not a recognized attribute. The correct
key is "byline":"By". The "By" prefix will silently  
 not render.

5. wp:site-title in header should use {"level":0}

Currently no level is set, so it defaults to 1 and renders as

  <h1>. This puts an <h1> on every page (in addition to the post
  title), which harms SEO. Set "level":0 to render it as a <p> tag,
   matching the Twenty Twenty-Four and Twenty Twenty-Five   
  convention.

6. Footer wp:navigation blocks missing "overlayMenu":"never"

The default is "mobile", which means a hamburger icon may appear in the footer
columns on small screens. Add "overlayMenu":"never" to both footer navigation
blocks.

---

Theme.json Issues

7. Three color palette entries share #193540

primary-dark, dark, and text are all #193540. dark and  
 primary-dark are completely redundant. Templates use both  
 interchangeably. Consolidate to one slug.

8. cream is #9fd4b3 — that's green, not cream

This is the global background color. The naming will confuse  
 anyone editing the theme in the Site Editor.

---

Minor / Structural Issues

- parts/comments.html and parts/post-meta.html — both 0 bytes. Will render
  empty.
- All 7 patterns/\*.php files — PHP docblock headers only, no block content
  inside. call-to-action.php, footer-default.php, and post-meta.php declare
  Categories metadata suggesting they were meant to have content.
- URL inconsistency — "find a registry" links go to /registry/, /registries,
  and /registry/create across different templates. One will 404.
