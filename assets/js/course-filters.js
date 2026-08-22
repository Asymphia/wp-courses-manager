document.addEventListener('DOMContentLoaded', () => {
    const wrap = document.querySelector('.kursy-wrapper')

    if (!wrap) {
        return
    }

    const resultsBox = wrap.querySelector('.kurs-results')
    const countBox = wrap.querySelector('.kurs-result-count')
    const lockedCategory = wrap.dataset.lockedCategory || ''
    const take = wrap.dataset.take || 9
    const pagination = wrap.dataset.pagination === '1'
    let debounceTimer = null

    wrap.querySelectorAll('.k-checkgroup input[type="checkbox"]').forEach(cb => {
        cb.addEventListener('change', () => fetchResults(1))
    })

    const getCheckedValues = (filterKey) => {
        const group = wrap.querySelector('.k-checkgroup[data-filter="' + filterKey + '"]')

        if (!group) {
            return []
        }

        return [...group.querySelectorAll('input[type="checkbox"]:checked')].map(cb => cb.value)
    }

    const fetchResults = (page) => {
        const body = new URLSearchParams()

        body.append('action', 'wp_filter_kursy')
        body.append('nonce', wpKursy.nonce)
        body.append('take', take)
        body.append('pagination', pagination ? '1' : '0')
        body.append('paged', page || 1)

        const sEl = wrap.querySelector('[data-filter="s"]')

        if (sEl) {
            body.append('s', sEl.value || '')
        }

        const sortEl = wrap.querySelector('[data-filter="sort"]')
        body.append('sort', sortEl ? sortEl.value : 'data_kursu_asc')

        ['data_od', 'data_do'].forEach(k => {
            const el = wrap.querySelector('[data-filter="' + k + '"]')

            if (el) {
                body.append(k, el.value || '')
            }
        })

        const katVals = lockedCategory ? [lockedCategory] : getCheckedValues('kategoria')

        katVals.forEach(v => body.append('kategoria[]', v))
        ['miejsce', 'organizator', 'wykladowca'].forEach(key => {
            getCheckedValues(key).forEach(v => body.append(key + '[]', v))
        })

        resultsBox.style.opacity = '0.5'

        fetch(wpKursy.ajaxUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        }).then(r => r.json())
          .then(res => {
              if (res.success) {
                  resultsBox.innerHTML = res.data.html
                  resultsBox.style.opacity = '1'
                  bindPagination()
                  updateCount()
              }
          })
    }

    const updateCount = () => {
        const holder = resultsBox.querySelector('.kurs-count-holder')

        if (!holder || !countBox) {
            return
        }

        const n = parseInt(holder.dataset.count || '0', 10)
        const word = n === 1 ? 'kurs' : (n % 10 >= 2 && n % 10 <= 4 && (n % 100 < 10 || n % 100 >= 20) ? 'kursy' : 'kursów')

        countBox.textContent = 'Znaleziono ' + n + ' ' + word
    }

    const bindPagination = () => {
        resultsBox.querySelectorAll('.k-page').forEach(btn => {
            btn.addEventListener('click', () => {
                fetchResults(parseInt(btn.dataset.page, 10))
                wrap.scrollIntoView({ behavior: 'smooth', block: 'start' })
            })
        })
    }

    const sEl = wrap.querySelector('[data-filter="s"]')

    if (sEl) {
        sEl.addEventListener('input', () => {
            clearTimeout(debounceTimer)
            debounceTimer = setTimeout(() => fetchResults(1), 400)
        })
    }

    wrap.querySelectorAll('select[data-filter], input[type="date"][data-filter]').forEach(el => {
        el.addEventListener('change', () => fetchResults(1))
    })

    const clearBtn = wrap.querySelector('.k-clear')

    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            if (sEl) {
                sEl.value = ''
            }

            wrap.querySelectorAll('input[type="date"]').forEach(el => el.value = '')
            wrap.querySelectorAll('.k-checkgroup input[type="checkbox"]').forEach(cb => cb.checked = false)

            const sortEl = wrap.querySelector('[data-filter="sort"]')

            if (sortEl) {
                sortEl.selectedIndex = 0
            }

            fetchResults(1)
        })
    }

    bindPagination()
    updateCount()
})