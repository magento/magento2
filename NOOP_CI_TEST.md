# CI Baseline Probe

This branch adds only this file, no code changes, to check whether the
Magento CI pipeline (Static Tests / Unit Tests / Functional Tests) is
green on an unmodified `2.4-develop` checkout.

Context: PR #41152 (wishlist section-count stale-cache fix) failed
Unit Tests and Functional Tests CE with results unrelated to the diff
(0 failed / 0 broken PHPUnit assertions; 4 pre-existing broken MFTF
tests also failing on unrelated PRs #41164 and #41171). This branch is
a control to confirm those failures are pre-existing CI flakiness, not
caused by the wishlist fix.
